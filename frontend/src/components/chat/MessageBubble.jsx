import React from 'react';
import { ROLE_CUSTOMER, ROLE_AI, ROLE_AGENT } from '../../lib/constants';

export default function MessageBubble({ message }) {
    const isCustomer = message.role === ROLE_CUSTOMER;
    const isAI = message.role === ROLE_AI;
    const isAgent = message.role === ROLE_AGENT;

    const bubbleColor = isCustomer ? '#1E293B' : isAI ? '#164E3D' : '#1E3A5F';
    const align = isCustomer ? 'flex-start' : 'flex-end';
    const label = isAI ? 'AI' : isAgent ? 'Agent' : null;

    const time = message.created_at
        ? new Date(message.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
        : '';

    return (
        <div style={{ display: 'flex', justifyContent: align, marginBottom: 6, padding: '0 16px' }}>
            <div style={{
                maxWidth: '70%', padding: '10px 14px', borderRadius: 14,
                background: bubbleColor, border: `1px solid ${isCustomer ? '#334155' : 'transparent'}`,
                position: 'relative',
            }}>
                {label && (
                    <div style={{ fontSize: '0.65rem', fontWeight: 700, color: isAI ? '#4ADE80' : '#60A5FA', marginBottom: 2 }}>
                        {label}
                    </div>
                )}
                <div style={{ fontSize: '0.88rem', color: '#E2E8F0', lineHeight: 1.5, whiteSpace: 'pre-wrap', wordBreak: 'break-word' }}>
                    {message.content}
                </div>
                <div style={{ fontSize: '0.65rem', color: '#64748B', textAlign: 'right', marginTop: 4 }}>
                    {time}
                </div>
            </div>
        </div>
    );
}
