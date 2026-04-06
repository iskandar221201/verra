import React, { useState, useEffect } from 'react';
import {
    GetFAQs, SaveFAQ, DeleteFAQ, GetProducts, SaveProduct, DeleteProduct,
    GetSOPs, SaveSOP, DeleteSOP, GetNotes, SaveNote, DeleteNote
} from '../../wailsjs/go/main/App';
import { BookOpen, Plus } from 'lucide-react';
import FAQTab from '../components/knowledge/FAQTab';
import ProductTab from '../components/knowledge/ProductTab';
import SOPTab from '../components/knowledge/SOPTab';
import NotesTab from '../components/knowledge/NotesTab';

const TABS = [
    { id: 'faq', label: 'FAQ' },
    { id: 'products', label: 'Produk' },
    { id: 'sops', label: 'SOP' },
    { id: 'notes', label: 'Catatan' },
];

export default function KnowledgePage() {
    const [activeTab, setActiveTab] = useState('faq');

    return (
        <div style={{ flex: 1, display: 'flex', flexDirection: 'column', height: '100%', overflow: 'hidden' }}>
            {/* Page Header */}
            <div style={{
                padding: '20px 24px 0', display: 'flex', alignItems: 'center', gap: 12,
            }}>
                <BookOpen size={22} color="#3B82F6" />
                <h2 style={{ margin: 0, fontSize: '1.2rem', color: '#E2E8F0', fontWeight: 700 }}>Knowledge Base</h2>
            </div>

            {/* Tabs */}
            <div style={{
                display: 'flex', gap: 4, padding: '16px 24px 0',
                borderBottom: '1px solid #1E293B',
            }}>
                {TABS.map((tab) => (
                    <button
                        key={tab.id}
                        onClick={() => setActiveTab(tab.id)}
                        style={{
                            padding: '8px 16px', borderRadius: '8px 8px 0 0',
                            border: 'none', cursor: 'pointer',
                            background: activeTab === tab.id ? '#1E293B' : 'transparent',
                            color: activeTab === tab.id ? '#E2E8F0' : '#64748B',
                            fontSize: '0.85rem', fontWeight: activeTab === tab.id ? 600 : 400,
                            borderBottom: activeTab === tab.id ? '2px solid #3B82F6' : '2px solid transparent',
                            transition: 'all 0.15s',
                        }}
                    >
                        {tab.label}
                    </button>
                ))}
            </div>

            {/* Tab Content */}
            <div style={{ flex: 1, overflow: 'auto', padding: '20px 24px' }}>
                {activeTab === 'faq' && <FAQTab />}
                {activeTab === 'products' && <ProductTab />}
                {activeTab === 'sops' && <SOPTab />}
                {activeTab === 'notes' && <NotesTab />}
            </div>
        </div>
    );
}
