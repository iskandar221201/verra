import React from 'react';
import { User, Clock, Tag } from 'lucide-react';
import StatusBadge from '../ui/StatusBadge';
import { formatRelativeTime } from '../../lib/format';

export default function DetailPanel({ conversation }) {
    if (!conversation) return null;

    return (
        <div style={{
            width: 300, minWidth: 300, height: '100%',
            background: '#0F172A', borderLeft: '1px solid #1E293B',
            padding: '20px', overflowY: 'auto',
        }}>
            {/* Customer Info */}
            <div style={{ textAlign: 'center', marginBottom: 24 }}>
                <div style={{
                    width: 64, height: 64, borderRadius: '50%',
                    background: 'linear-gradient(135deg, #3B82F640, #3B82F620)',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    fontSize: '1.5rem', fontWeight: 700, color: '#3B82F6',
                    margin: '0 auto 12px',
                }}>
                    {(conversation.customer_name || '?')[0].toUpperCase()}
                </div>
                <h3 style={{ margin: '0 0 4px', fontSize: '1rem', color: '#E2E8F0', fontWeight: 600 }}>
                    {conversation.customer_name || 'Unknown'}
                </h3>
                <div style={{ fontSize: '0.75rem', color: '#64748B', marginBottom: 8 }}>
                    {conversation.id}
                </div>
                <StatusBadge status={conversation.status} />
            </div>

            {/* Details */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                <DetailRow icon={<Clock size={14} />} label="Pesan Terakhir" value={formatRelativeTime(conversation.last_message_at)} />
                <DetailRow icon={<Tag size={14} />} label="Status" value={conversation.status} />
            </div>
        </div>
    );
}

function DetailRow({ icon, label, value }) {
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <div style={{ color: '#475569' }}>{icon}</div>
            <div>
                <div style={{ fontSize: '0.7rem', color: '#64748B', textTransform: 'uppercase', fontWeight: 600 }}>{label}</div>
                <div style={{ fontSize: '0.82rem', color: '#CBD5E1' }}>{value || '-'}</div>
            </div>
        </div>
    );
}
