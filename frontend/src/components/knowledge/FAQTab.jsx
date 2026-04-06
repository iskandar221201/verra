import React, { useState, useEffect } from 'react';
import { GetFAQs, SaveFAQ, DeleteFAQ } from '../../../wailsjs/go/main/App';
import Modal from '../ui/Modal';
import ConfirmDialog from '../ui/ConfirmDialog';
import Toggle from '../ui/Toggle';
import EmptyState from '../ui/EmptyState';
import { Plus, Edit2, Trash2 } from 'lucide-react';
import toast from 'react-hot-toast';

export default function FAQTab() {
    const [faqs, setFAQs] = useState([]);
    const [showModal, setShowModal] = useState(false);
    const [editItem, setEditItem] = useState(null);
    const [deleteId, setDeleteId] = useState(null);

    const loadData = () => GetFAQs().then(f => setFAQs(f || [])).catch(console.error);
    useEffect(() => { loadData(); }, []);

    const handleSave = async (faq) => {
        try {
            await SaveFAQ(faq);
            toast.success(faq.id ? 'FAQ diperbarui' : 'FAQ ditambahkan');
            setShowModal(false); setEditItem(null); loadData();
        } catch (err) { toast.error('Gagal menyimpan FAQ'); }
    };

    const handleDelete = async () => {
        try {
            await DeleteFAQ(deleteId);
            toast.success('FAQ dihapus'); setDeleteId(null); loadData();
        } catch (err) { toast.error('Gagal menghapus'); }
    };

    return (
        <div>
            <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: 16 }}>
                <button onClick={() => { setEditItem(null); setShowModal(true); }} style={addBtnStyle}>
                    <Plus size={16} /> Tambah FAQ
                </button>
            </div>

            {faqs.length === 0 ? (
                <EmptyState icon="❓" title="Belum ada FAQ" description="Tambahkan FAQ untuk membantu AI menjawab pertanyaan." />
            ) : (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                    {faqs.map((f) => (
                        <div key={f.id} style={rowStyle}>
                            <div style={{ flex: 1 }}>
                                <div style={{ fontWeight: 600, color: '#E2E8F0', fontSize: '0.88rem' }}>{f.question}</div>
                                <div style={{ color: '#94A3B8', fontSize: '0.8rem', marginTop: 2 }}>{f.answer.slice(0, 80)}...</div>
                            </div>
                            <span style={tagStyle}>{f.category}</span>
                            <Toggle checked={f.is_active} onChange={(v) => handleSave({ ...f, is_active: v })} />
                            <button onClick={() => { setEditItem(f); setShowModal(true); }} style={iconBtnStyle}><Edit2 size={14} /></button>
                            <button onClick={() => setDeleteId(f.id)} style={{ ...iconBtnStyle, color: '#EF4444' }}><Trash2 size={14} /></button>
                        </div>
                    ))}
                </div>
            )}

            <FAQModal isOpen={showModal} onClose={() => { setShowModal(false); setEditItem(null); }} onSave={handleSave} initial={editItem} />
            <ConfirmDialog isOpen={!!deleteId} title="Hapus FAQ?" message="FAQ ini akan dihapus permanen." onConfirm={handleDelete} onCancel={() => setDeleteId(null)} />
        </div>
    );
}

function FAQModal({ isOpen, onClose, onSave, initial }) {
    const [question, setQuestion] = useState('');
    const [answer, setAnswer] = useState('');
    const [category, setCategory] = useState('umum');

    useEffect(() => {
        if (initial) { setQuestion(initial.question); setAnswer(initial.answer); setCategory(initial.category); }
        else { setQuestion(''); setAnswer(''); setCategory('umum'); }
    }, [initial, isOpen]);

    return (
        <Modal isOpen={isOpen} onClose={onClose} title={initial ? 'Edit FAQ' : 'Tambah FAQ'}>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                <label style={labelStyle}>Pertanyaan
                    <input value={question} onChange={e => setQuestion(e.target.value)} style={inputStyle} placeholder="Contoh: Bagaimana cara order?" />
                </label>
                <label style={labelStyle}>Jawaban
                    <textarea value={answer} onChange={e => setAnswer(e.target.value)} rows={4} style={{ ...inputStyle, resize: 'vertical' }} placeholder="Jawaban lengkap..." />
                </label>
                <label style={labelStyle}>Kategori
                    <input value={category} onChange={e => setCategory(e.target.value)} style={inputStyle} />
                </label>
                <button onClick={() => onSave({ id: initial?.id || 0, question, answer, category, sort_order: initial?.sort_order || 0, is_active: initial?.is_active ?? true })}
                    disabled={!question.trim() || !answer.trim()} style={{ ...addBtnStyle, width: '100%', justifyContent: 'center', opacity: !question.trim() || !answer.trim() ? 0.5 : 1 }}>
                    Simpan
                </button>
            </div>
        </Modal>
    );
}

const addBtnStyle = { display: 'flex', alignItems: 'center', gap: 6, padding: '8px 16px', borderRadius: 8, border: 'none', background: '#3B82F6', color: '#fff', cursor: 'pointer', fontSize: '0.82rem', fontWeight: 600 };
const rowStyle = { display: 'flex', alignItems: 'center', gap: 12, padding: '12px 16px', borderRadius: 10, background: '#1E293B', border: '1px solid #334155' };
const iconBtnStyle = { background: 'none', border: 'none', color: '#64748B', cursor: 'pointer', padding: 4 };
const tagStyle = { fontSize: '0.7rem', padding: '2px 8px', borderRadius: 6, background: '#0F172A', color: '#64748B', border: '1px solid #334155' };
const labelStyle = { display: 'flex', flexDirection: 'column', gap: 4, fontSize: '0.82rem', color: '#94A3B8', fontWeight: 500 };
const inputStyle = { padding: '10px 12px', borderRadius: 8, border: '1px solid #334155', background: '#0F172A', color: '#E2E8F0', fontSize: '0.88rem', outline: 'none' };
