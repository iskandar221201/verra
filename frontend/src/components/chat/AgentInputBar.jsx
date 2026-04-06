import React, { useState } from 'react';
import { Send, CheckCircle } from 'lucide-react';

export default function AgentInputBar({ onSend, onResolve, onSwitchToAI }) {
    const [text, setText] = useState('');
    const [sending, setSending] = useState(false);

    const handleSend = async () => {
        const trimmed = text.trim();
        if (!trimmed || sending) return;
        setSending(true);
        try {
            await onSend(trimmed);
            setText('');
        } catch (err) {
            console.error('AgentInputBar send:', err);
        } finally {
            setSending(false);
        }
    };

    const handleKeyDown = (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleSend();
        }
    };

    return (
        <div style={{
            display: 'flex', alignItems: 'center', gap: 8,
            padding: '12px 16px', background: '#0F172A', borderTop: '1px solid #1E293B',
        }}>
            <input
                value={text}
                onChange={(e) => setText(e.target.value)}
                onKeyDown={handleKeyDown}
                placeholder="Tulis pesan sebagai agent..."
                disabled={sending}
                style={{
                    flex: 1, padding: '10px 14px', borderRadius: 10,
                    border: '1px solid #334155', background: '#1E293B',
                    color: '#E2E8F0', fontSize: '0.88rem', outline: 'none',
                }}
            />
            <button onClick={handleSend} disabled={sending || !text.trim()} style={{
                padding: '10px', borderRadius: 10, border: 'none',
                background: '#3B82F6', color: '#fff', cursor: 'pointer',
                opacity: sending || !text.trim() ? 0.5 : 1,
                display: 'flex', alignItems: 'center',
            }}>
                <Send size={18} />
            </button>
            <button onClick={onResolve} title="Selesaikan percakapan" style={{
                padding: '10px', borderRadius: 10, border: '1px solid #334155',
                background: 'transparent', color: '#22C55E', cursor: 'pointer',
                display: 'flex', alignItems: 'center',
            }}>
                <CheckCircle size={18} />
            </button>
            <button onClick={onSwitchToAI} title="Beralih kembali ke AI" style={{
                padding: '10px', borderRadius: 10, border: '1px solid #334155',
                background: 'transparent', color: '#3B82F6', cursor: 'pointer',
                display: 'flex', alignItems: 'center', fontSize: '0.75rem', fontWeight: 600,
            }}>
                AI
            </button>
        </div>
    );
}
