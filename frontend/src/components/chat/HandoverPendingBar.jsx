import React from 'react';
import { UserCheck } from 'lucide-react';

export default function HandoverPendingBar({ onClaim, onSwitchToAI }) {
    return (
        <div style={{
            display: 'flex', alignItems: 'center', justifyContent: 'space-between',
            padding: '12px 20px', background: '#2D1F0E', borderTop: '1px solid #6B4F1D',
        }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                <span style={{
                    width: 8, height: 8, borderRadius: '50%', background: '#F59E0B',
                    animation: 'pulse 2s infinite',
                }} />
                <span style={{ fontSize: '0.82rem', color: '#FCD34D', fontWeight: 500 }}>
                    Customer meminta bantuan agent
                </span>
            </div>
            <div style={{ display: 'flex', gap: 8 }}>
                <button onClick={onSwitchToAI} style={{
                    padding: '8px 12px', borderRadius: 8, border: '1px solid #6B4F1D',
                    background: 'transparent', color: '#FCD34D', cursor: 'pointer',
                    fontSize: '0.82rem', fontWeight: 600,
                }}>
                    Beralih ke AI
                </button>
                <button onClick={onClaim} style={{
                    display: 'flex', alignItems: 'center', gap: 6,
                    padding: '8px 16px', borderRadius: 8, border: 'none',
                    background: '#F59E0B', color: '#1C1917', cursor: 'pointer',
                    fontSize: '0.82rem', fontWeight: 600,
                }}>
                    <UserCheck size={16} />
                    Ambil Alih
                </button>
            </div>
        </div>
    );
}
