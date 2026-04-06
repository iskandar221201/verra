import React from 'react';
import StatusBadge from '../ui/StatusBadge';
import { Phone, MoreVertical } from 'lucide-react';

export default function ChatHeader({ conversation }) {
    if (!conversation) return null;

    return (
        <div style={{
            display: 'flex', alignItems: 'center', justifyContent: 'space-between',
            padding: '12px 20px', borderBottom: '1px solid #1E293B',
            background: '#0F172A',
        }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                <div style={{
                    width: 36, height: 36, borderRadius: '50%',
                    background: 'linear-gradient(135deg, #3B82F640, #3B82F620)',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    fontSize: '0.85rem', fontWeight: 700, color: '#3B82F6',
                }}>
                    {(conversation.customer_name || '?')[0].toUpperCase()}
                </div>
                <div>
                    <div style={{ fontWeight: 600, fontSize: '0.9rem', color: '#E2E8F0' }}>
                        {conversation.customer_name || 'Unknown'}
                    </div>
                    <StatusBadge status={conversation.status} />
                </div>
            </div>
            <div style={{ display: 'flex', gap: 8 }}>
                <button style={{
                    background: 'none', border: 'none', color: '#64748B', cursor: 'pointer', padding: 6, borderRadius: 8,
                }}>
                    <MoreVertical size={18} />
                </button>
            </div>
        </div>
    );
}
