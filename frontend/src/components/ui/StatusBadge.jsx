import React from 'react';
import { STATUS_COLORS, STATUS_LABELS } from '../../lib/constants';

export default function StatusBadge({ status }) {
    const color = STATUS_COLORS[status] || '#9CA3AF';
    const label = STATUS_LABELS[status] || status;

    return (
        <span
            className="status-badge"
            style={{
                display: 'inline-flex',
                alignItems: 'center',
                gap: '6px',
                padding: '2px 10px',
                borderRadius: '999px',
                fontSize: '0.7rem',
                fontWeight: 600,
                letterSpacing: '0.02em',
                color: color,
                background: `${color}18`,
                border: `1px solid ${color}30`,
            }}
        >
            <span style={{
                width: 6, height: 6, borderRadius: '50%',
                background: color,
                animation: status === 'ai' ? 'pulse 2s infinite' : 'none',
            }} />
            {label}
        </span>
    );
}
