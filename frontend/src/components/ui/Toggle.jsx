import React from 'react';

export default function Toggle({ checked, onChange, label }) {
    return (
        <label style={{ display: 'inline-flex', alignItems: 'center', gap: 8, cursor: 'pointer' }}>
            <div onClick={() => onChange(!checked)} style={{
                width: 40, height: 22, borderRadius: 11,
                background: checked ? '#22C55E' : '#475569',
                position: 'relative', transition: 'background 0.2s',
                cursor: 'pointer',
            }}>
                <div style={{
                    width: 18, height: 18, borderRadius: '50%',
                    background: '#fff', position: 'absolute', top: 2,
                    left: checked ? 20 : 2, transition: 'left 0.2s',
                    boxShadow: '0 1px 3px rgba(0,0,0,0.3)',
                }} />
            </div>
            {label && <span style={{ fontSize: '0.85rem', color: '#CBD5E1' }}>{label}</span>}
        </label>
    );
}
