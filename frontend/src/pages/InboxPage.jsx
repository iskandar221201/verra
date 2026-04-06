import React from 'react';
import useAppStore from '../store/appStore';
import ChatView from '../components/chat/ChatView';
import DetailPanel from '../components/layout/DetailPanel';


export default function InboxPage() {
    const conversations = useAppStore((s) => s.conversations);
    const selectedConvId = useAppStore((s) => s.selectedConvId);
    const selectedConv = conversations.find((c) => c.id === selectedConvId) || null;

    return (
        <div style={{ display: 'flex', flex: 1, height: '100%', overflow: 'hidden' }}>
            <div style={{ flex: 1, display: 'flex', flexDirection: 'column', overflow: 'hidden' }}>
                <ChatView conversation={selectedConv} />
            </div>
            {selectedConv && <DetailPanel conversation={selectedConv} />}
        </div>
    );
}
