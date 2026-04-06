import React from 'react';
import ChatHeader from './ChatHeader';
import MessageBubble from './MessageBubble';
import AIActiveBar from './AIActiveBar';
import HandoverPendingBar from './HandoverPendingBar';
import AgentInputBar from './AgentInputBar';
import EmptyState from '../ui/EmptyState';
import { useConversation } from '../../hooks/useConversation';
import { STATUS_AI, STATUS_HANDOVER_PENDING, STATUS_HUMAN } from '../../lib/constants';
import { AgentClaimHandover, AgentSendMessage, AgentResolveConversation, AgentSwitchToAI } from '../../../wailsjs/go/main/App';
import toast from 'react-hot-toast';

export default function ChatView({ conversation }) {
    const { messages, loading, bottomRef } = useConversation(conversation?.id);

    if (!conversation) {
        return (
            <EmptyState
                icon="💬"
                title="Pilih percakapan"
                description="Klik salah satu percakapan di sidebar untuk mulai."
            />
        );
    }

    const handleClaim = async () => {
        try {
            await AgentClaimHandover(conversation.id);
            toast.success('Percakapan berhasil diambil alih');
        } catch (err) {
            toast.error('Gagal mengambil alih percakapan');
        }
    };

    const handleSend = async (text) => {
        await AgentSendMessage(conversation.id, text);
    };

    const handleResolve = async () => {
        try {
            await AgentResolveConversation(conversation.id);
            toast.success('Percakapan diselesaikan');
        } catch (err) {
            toast.error('Gagal menyelesaikan percakapan');
        }
    };

    const handleSwitchToAI = async () => {
        try {
            await AgentSwitchToAI(conversation.id);
            toast.success('Kembali ke mode AI');
        } catch (err) {
            toast.error('Gagal beralih ke AI');
        }
    };

    return (
        <div style={{ display: 'flex', flexDirection: 'column', height: '100%', background: '#0B1121' }}>
            <ChatHeader conversation={conversation} />

            {/* Messages */}
            <div style={{ flex: 1, overflowY: 'auto', padding: '16px 0' }}>
                {loading && (
                    <div style={{ textAlign: 'center', padding: 20, color: '#64748B', fontSize: '0.85rem' }}>
                        Memuat pesan...
                    </div>
                )}
                {!loading && messages.length === 0 && (
                    <div style={{ textAlign: 'center', padding: 40, color: '#475569', fontSize: '0.85rem' }}>
                        Belum ada pesan
                    </div>
                )}
                {messages.map((msg) => (
                    <MessageBubble key={msg.id} message={msg} />
                ))}
                <div ref={bottomRef} />
            </div>

            {/* Bottom bar — single conditional render */}
            {conversation.status === STATUS_AI && <AIActiveBar />}
            {conversation.status === STATUS_HANDOVER_PENDING && <HandoverPendingBar onClaim={handleClaim} onSwitchToAI={handleSwitchToAI} />}
            {conversation.status === STATUS_HUMAN && <AgentInputBar onSend={handleSend} onResolve={handleResolve} onSwitchToAI={handleSwitchToAI} />}
        </div>
    );
}
