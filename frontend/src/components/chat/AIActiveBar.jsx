import React from 'react';
import { Bot } from 'lucide-react';

export default function AIActiveBar() {
    return (
        <div style={{
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            padding: '12px 20px', background: '#0F2918', borderTop: '1px solid #16532E',
            gap: 8,
        }}>
            <span style={{
                width: 8, height: 8, borderRadius: '50%', background: '#22C55E',
                animation: 'pulse 2s infinite',
            }} />
            <Bot size={16} color="#22C55E" />
            <span style={{ fontSize: '0.82rem', color: '#4ADE80', fontWeight: 500 }}>
                AI sedang aktif menangani percakapan ini
            </span>
        </div>
    );
}
