import React from 'react';
import { MessageSquare, BookOpen, Settings, Key, Wifi, WifiOff } from 'lucide-react';
import useAppStore from '../../store/appStore';
import InboxItem from '../inbox/InboxItem';

const NAV_ITEMS = [
    { id: 'inbox', label: 'Inbox', icon: MessageSquare },
    { id: 'knowledge', label: 'Knowledge', icon: BookOpen },
    { id: 'settings', label: 'Settings', icon: Settings },
    { id: 'apikeys', label: 'API Keys', icon: Key },
];

export default function Sidebar() {
    const activePage = useAppStore((s) => s.activePage);
    const setActivePage = useAppStore((s) => s.setActivePage);
    const conversations = useAppStore((s) => s.conversations);
    const selectedConvId = useAppStore((s) => s.selectedConvId);
    const setSelectedConvId = useAppStore((s) => s.setSelectedConvId);
    const waStatus = useAppStore((s) => s.waStatus);

    return (
        <div style={{
            width: 240, minWidth: 240, height: '100%',
            background: '#0F172A', borderRight: '1px solid #1E293B',
            display: 'flex', flexDirection: 'column',
        }}>
            {/* Header */}
            <div style={{
                padding: '16px', borderBottom: '1px solid #1E293B',
                display: 'flex', alignItems: 'center', justifyContent: 'space-between',
            }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                    <span style={{ fontSize: '1.2rem', fontWeight: 800, color: '#F1F5F9', letterSpacing: '-0.02em' }}>
                        Verra
                    </span>
                    <span style={{
                        fontSize: '0.6rem', padding: '2px 6px', borderRadius: 4,
                        background: '#1E293B', color: '#64748B', fontWeight: 600,
                    }}>AI</span>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
                    {waStatus === 'connected'
                        ? <Wifi size={14} color="#22C55E" />
                        : <WifiOff size={14} color="#EF4444" />
                    }
                    <span style={{ fontSize: '0.65rem', color: waStatus === 'connected' ? '#22C55E' : '#EF4444' }}>
                        {waStatus === 'connected' ? 'Online' : 'Offline'}
                    </span>
                </div>
            </div>

            {/* Navigation */}
            <div style={{ padding: '8px' }}>
                {NAV_ITEMS.map(({ id, label, icon: Icon }) => (
                    <button
                        key={id}
                        onClick={() => setActivePage(id)}
                        style={{
                            width: '100%', padding: '8px 12px', borderRadius: 8,
                            border: 'none', cursor: 'pointer',
                            background: activePage === id ? '#1E293B' : 'transparent',
                            color: activePage === id ? '#E2E8F0' : '#64748B',
                            display: 'flex', alignItems: 'center', gap: 8,
                            fontSize: '0.82rem', fontWeight: 500,
                            transition: 'all 0.15s',
                            marginBottom: 2,
                        }}
                    >
                        <Icon size={16} />
                        {label}
                    </button>
                ))}
            </div>

            {/* Inbox list (only when on inbox page) */}
            {activePage === 'inbox' && (
                <div style={{ flex: 1, overflowY: 'auto', borderTop: '1px solid #1E293B' }}>
                    {conversations.length === 0 ? (
                        <div style={{ padding: 20, textAlign: 'center', color: '#475569', fontSize: '0.8rem' }}>
                            Belum ada percakapan. Hubungkan WhatsApp untuk mulai.
                        </div>
                    ) : (
                        conversations.map((conv) => (
                            <InboxItem
                                key={conv.id}
                                conversation={conv}
                                isActive={conv.id === selectedConvId}
                                onClick={() => setSelectedConvId(conv.id)}
                            />
                        ))
                    )}
                </div>
            )}
        </div>
    );
}
