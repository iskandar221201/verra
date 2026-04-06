import React from 'react';
import { AlertTriangle } from 'lucide-react';

export default function ConfirmDialog({ isOpen, onConfirm, onCancel, title, message }) {
    if (!isOpen) return null;

    return (
        <div onClick={onCancel} style={{
            position: 'fixed', inset: 0, zIndex: 1100,
            background: 'rgba(0,0,0,0.5)', backdropFilter: 'blur(4px)',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
        }}>
            <div onClick={e => e.stopPropagation()} style={{
                background: '#1E293B', borderRadius: 16, width: 'min(400px, 90vw)',
                padding: '24px', boxShadow: '0 25px 50px rgba(0,0,0,0.5)',
                border: '1px solid #334155', textAlign: 'center',
            }}>
                <AlertTriangle size={40} color="#F59E0B" style={{ marginBottom: 12 }} />
                <h3 style={{ margin: '0 0 8px', fontSize: '1rem', color: '#F1F5F9' }}>{title}</h3>
                <p style={{ margin: '0 0 20px', fontSize: '0.85rem', color: '#94A3B8' }}>{message}</p>
                <div style={{ display: 'flex', gap: 12, justifyContent: 'center' }}>
                    <button onClick={onCancel} style={{
                        padding: '8px 20px', borderRadius: 8, border: '1px solid #475569',
                        background: 'transparent', color: '#CBD5E1', cursor: 'pointer', fontSize: '0.85rem',
                    }}>Batal</button>
                    <button onClick={onConfirm} style={{
                        padding: '8px 20px', borderRadius: 8, border: 'none',
                        background: '#EF4444', color: '#fff', cursor: 'pointer', fontSize: '0.85rem', fontWeight: 600,
                    }}>Hapus</button>
                </div>
            </div>
        </div>
    );
}
