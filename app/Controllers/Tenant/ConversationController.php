<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\ConversationModel;
use App\Models\WaChannelModel;
use App\Models\AgentMessageModel;

class ConversationController extends BaseController
{
    protected ConversationModel $conversationModel;
    protected WaChannelModel $channelModel;
    protected AgentMessageModel $agentMessageModel;

    public function __construct()
    {
        $this->conversationModel = new ConversationModel();
        $this->channelModel = new WaChannelModel();
        $this->agentMessageModel = new AgentMessageModel();
    }

    /**
     * List unique conversations
     *
     * @return string
     */
    public function index()
    {
        $search = $this->request->getGet('search');
        $channelFilter = $this->request->getGet('channel_id');

        $db = \Config\Database::connect();

        // Subquery to get the latest message ID for each unique conversation
        $subQuery = $db->table('conversations')
            ->select('MAX(id) as id');

        if ($this->tenant_id) {
            $subQuery->where('tenant_id', $this->tenant_id);
        }

        $subQuery->groupBy('channel_id, wa_number');

        if (!empty($search)) {
            $subQuery->like('wa_number', $search);
        }

        if (!empty($channelFilter)) {
            $subQuery->where('channel_id', $channelFilter);
        }

        $latestIds = $subQuery->get()->getResultArray();
        $ids = array_column($latestIds, 'id');

        if (empty($ids)) {
            $data = [
                'conversations' => [],
                'channels' => $this->channelModel->forTenant()->findAll(),
                'pager' => null,
                'search' => $search,
                'channelFilter' => $channelFilter,
            ];
        } else {
            // Main query to get conversation details
            $builder = $this->conversationModel->select('conversations.*, wa_channels.name as channel_name')
                ->join('wa_channels', 'wa_channels.id = conversations.channel_id')
                ->whereIn('conversations.id', $ids)
                ->orderBy('conversations.created_at', 'DESC');

            $conversations = $builder->paginate(10, 'default');

            // Add message count to each conversation
            foreach ($conversations as &$conv) {
                $countQuery = $this->conversationModel
                    ->where('channel_id', $conv['channel_id'])
                    ->where('wa_number', $conv['wa_number']);

                if ($this->tenant_id) {
                    $countQuery->where('tenant_id', $this->tenant_id);
                }

                $conv['message_count'] = $countQuery->countAllResults();
            }

            $data = [
                'conversations' => $conversations,
                'channels' => $this->channelModel->forTenant()->findAll(),
                'pager' => $this->conversationModel->pager,
                'search' => $search,
                'channelFilter' => $channelFilter,
            ];
        }

        return view('_layouts/tenant', [
            'title' => 'Conversations',
            'content' => view('tenant/conversations/index', $data),
        ]);
    }

    /**
     * Show chat history for a specific customer
     *
     * @param int $channelId
     * @param string $waNumber
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function show(int $channelId, string $waNumber)
    {
        // Validate channel belongs to tenant
        $channelQuery = $this->channelModel->where('id', $channelId);
        if ($this->tenant_id) {
            $channelQuery->where('tenant_id', $this->tenant_id);
        }
        $channel = $channelQuery->first();

        if (!$channel) {
            return redirect()->to('/conversations')->with('error', 'Channel tidak ditemukan.');
        }

        // Get all conversations
        $historyQuery = $this->conversationModel
            ->where('channel_id', $channelId)
            ->where('wa_number', $waNumber);

        if ($this->tenant_id) {
            $historyQuery->where('tenant_id', $this->tenant_id);
        }

        $history = $historyQuery->orderBy('created_at', 'ASC')->findAll();

        // Get agent messages to identify which assistant messages are from agents
        $agentMessageQuery = $this->agentMessageModel
            ->select('agent_messages.message, agent_messages.sent_at, users.full_name as agent_name')
            ->join('users', 'users.id = agent_messages.agent_id')
            ->where('agent_messages.channel_id', $channelId)
            ->where('agent_messages.wa_number', $waNumber);

        if ($this->tenant_id) {
            $agentMessageQuery->where('agent_messages.tenant_id', $this->tenant_id);
        }

        $agentMessages = $agentMessageQuery->findAll();

        // Map agent messages by their content and time (roughly) or just check role
        // Since AgentChatService saves exactly the same message to both, we can match them.
        foreach ($history as &$msg) {
            $msg['agent_name'] = null;
            if ($msg['role'] === 'assistant') {
                foreach ($agentMessages as $agentMsg) {
                    // Match message content. In production we might need a better link like FK.
                    if ($msg['message'] === $agentMsg['message']) {
                        $msg['agent_name'] = $agentMsg['agent_name'];
                        break;
                    }
                }
            }
        }

        $data = [
            'channel' => $channel,
            'waNumber' => $waNumber,
            'history' => $history,
        ];

        return view('_layouts/tenant', [
            'title' => 'Chat History: ' . $waNumber,
            'content' => view('tenant/conversations/show', $data),
        ]);
    }
}
