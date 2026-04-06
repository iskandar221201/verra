import React, { useState } from 'react';
import { X } from 'lucide-react';

export default function TagInput({ tags = [], onChange, placeholder = 'Tambah kata kunci...' }) {
    const [input, setInput] = useState('');

    const addTag = () => {
        const value = input.trim();
        if (value && !tags.includes(value)) {
            onChange([...tags, value]);
            setInput('');
        }
    };

    const removeTag = (index) => {
        onChange(tags.filter((_, i) => i !== index));
    };

    const handleKeyDown = (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addTag();
        }
    };

    return (
        <div style={{
            display: 'flex', flexWrap: 'wrap', gap: 6, padding: '8px 12px',
            background: '#0F172A', border: '1px solid #334155', borderRadius: 8,
            minHeight: 40, alignItems: 'center',
        }}>
            {tags.map((tag, i) => (
                <span key={i} style={{
                    display: 'inline-flex', alignItems: 'center', gap: 4,
                    padding: '2px 8px', borderRadius: 6, fontSize: '0.8rem',
                    background: '#1E293B', color: '#94A3B8', border: '1px solid #334155',
                }}>
                    {tag}
                    <X size={12} style={{ cursor: 'pointer' }} onClick={() => removeTag(i)} />
                </span>
            ))}
            <input
                value={input}
                onChange={(e) => setInput(e.target.value)}
                onKeyDown={handleKeyDown}
                onBlur={addTag}
                placeholder={tags.length === 0 ? placeholder : ''}
                style={{
                    background: 'none', border: 'none', outline: 'none',
                    color: '#E2E8F0', fontSize: '0.85rem', flex: 1, minWidth: 100,
                }}
            />
        </div>
    );
}
