import React from 'react';
import StatusBadge from '../ui/StatusBadge';
import { formatTimestamp, truncate } from '../../lib/format';
import { STATUS_COLORS } from '../../lib/constants';

export default function InboxItem({ conversation, isActive, onClick }) {
    const { customer_name, last_message, last_message_at, status, unread_count } = conversation;

    return (
        <div
            onClick={onClick}
            className="inbox-item"
            style={{
                display: 'flex', alignItems: 'center', gap: 12,
                padding: '12px 16px', cursor: 'pointer',
                background: isActive ? '#1E293B' : 'transparent',
                borderLeft: isActive ? `3px solid ${STATUS_COLORS[status] || '#3B82F6'}` : '3px solid transparent',
                transition: 'all 0.15s ease',
            }}
        >
            {/* Avatar */}
            <div style={{
                width: 40, height: 40, borderRadius: '50%',
                background: `linear-gradient(135deg, ${STATUS_COLORS[status] || '#3B82F6'}40, ${STATUS_COLORS[status] || '#3B82F6'}20)`,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                fontSize: '0.9rem', fontWeight: 700, color: STATUS_COLORS[status] || '#3B82F6',
                flexShrink: 0,
            }}>
                {(customer_name || '?')[0].toUpperCase()}
            </div>

            {/* Content */}
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 2 }}>
                    <span style={{ fontWeight: 600, fontSize: '0.85rem', color: '#E2E8F0', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                        {customer_name || 'Unknown'}
                    </span>
                    <span style={{ fontSize: '0.7rem', color: '#64748B', flexShrink: 0, marginLeft: 8 }}>
                        {formatTimestamp(last_message_at)}
                    </span>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <span style={{ fontSize: '0.78rem', color: '#94A3B8', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                        {truncate(last_message, 40)}
                    </span>
                    <div style={{ flexShrink: 0, marginLeft: 8 }}>
                        <StatusBadge status={status} />
                    </div>
                </div>
            </div>
        </div>
    );
}
