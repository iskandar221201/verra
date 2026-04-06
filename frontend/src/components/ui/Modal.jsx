import React from 'react';
import { X } from 'lucide-react';

export default function Modal({ isOpen, onClose, title, children, width = 520 }) {
    if (!isOpen) return null;

    return (
        <div className="modal-overlay" onClick={onClose} style={{
            position: 'fixed', inset: 0, zIndex: 1000,
            background: 'rgba(0,0,0,0.5)', backdropFilter: 'blur(4px)',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
        }}>
            <div className="modal-content" onClick={e => e.stopPropagation()} style={{
                background: '#1E293B', borderRadius: 16, width: `min(${width}px, 90vw)`,
                maxHeight: '85vh', overflow: 'hidden', display: 'flex', flexDirection: 'column',
                boxShadow: '0 25px 50px rgba(0,0,0,0.5)', border: '1px solid #334155',
            }}>
                <div style={{
                    display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                    padding: '16px 20px', borderBottom: '1px solid #334155',
                }}>
                    <h3 style={{ margin: 0, fontSize: '1rem', fontWeight: 600, color: '#F1F5F9' }}>{title}</h3>
                    <button onClick={onClose} style={{
                        background: 'none', border: 'none', color: '#94A3B8', cursor: 'pointer',
                        padding: 4, borderRadius: 8, display: 'flex',
                    }}>
                        <X size={18} />
                    </button>
                </div>
                <div style={{ padding: '20px', overflowY: 'auto', flex: 1 }}>
                    {children}
                </div>
            </div>
        </div>
    );
}
