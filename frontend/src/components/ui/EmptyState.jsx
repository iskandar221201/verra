import React from 'react';

export default function EmptyState({ icon, title, description }) {
    return (
        <div style={{
            display: 'flex', flexDirection: 'column', alignItems: 'center',
            justifyContent: 'center', height: '100%', padding: '48px 24px',
            color: '#9CA3AF', textAlign: 'center',
        }}>
            {icon && <div style={{ fontSize: '3rem', marginBottom: 16, opacity: 0.5 }}>{icon}</div>}
            <h3 style={{ margin: 0, fontSize: '1.1rem', color: '#6B7280', fontWeight: 600 }}>{title}</h3>
            {description && <p style={{ margin: '8px 0 0', fontSize: '0.85rem', maxWidth: 320 }}>{description}</p>}
        </div>
    );
}
