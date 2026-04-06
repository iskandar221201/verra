import React, { useState, useEffect } from 'react';
import { GetNotes, SaveNote, DeleteNote } from '../../../wailsjs/go/main/App';
import Modal from '../ui/Modal';
import ConfirmDialog from '../ui/ConfirmDialog';
import Toggle from '../ui/Toggle';
import EmptyState from '../ui/EmptyState';
import { Plus, Edit2, Trash2, FileText } from 'lucide-react';
import toast from 'react-hot-toast';

export default function NotesTab() {
    const [notes, setNotes] = useState([]);
    const [showModal, setShowModal] = useState(false);
    const [editItem, setEditItem] = useState(null);
    const [deleteId, setDeleteId] = useState(null);

    const loadData = () => GetNotes().then(n => setNotes(n || [])).catch(console.error);
    useEffect(() => { loadData(); }, []);

    const handleSave = async (note) => {
        try { await SaveNote(note); toast.success(note.id ? 'Catatan diperbarui' : 'Catatan ditambahkan'); setShowModal(false); setEditItem(null); loadData(); }
        catch (err) { toast.error('Gagal menyimpan catatan'); }
    };

    const handleDelete = async () => {
        try { await DeleteNote(deleteId); toast.success('Catatan dihapus'); setDeleteId(null); loadData(); }
        catch (err) { toast.error('Gagal menghapus'); }
    };

    return (
        <div>
            <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: 16 }}>
                <button onClick={() => { setEditItem(null); setShowModal(true); }} style={btnStyle}><Plus size={16} /> Tambah Catatan</button>
            </div>
            {notes.length === 0 ? (
                <EmptyState icon="📝" title="Belum ada Catatan" description="Tambahkan catatan tambahan untuk memperkaya konteks AI." />
            ) : (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                    {notes.map((n) => (
                        <div key={n.id} style={rowStyle}>
                            <FileText size={16} color="#64748B" />
                            <div style={{ flex: 1 }}>
                                <div style={{ fontWeight: 600, color: '#E2E8F0', fontSize: '0.88rem' }}>{n.title}</div>
                                <div style={{ color: '#94A3B8', fontSize: '0.8rem' }}>{n.content.slice(0, 80)}{n.content.length > 80 ? '...' : ''}</div>
                            </div>
                            <span style={tagStyle}>{n.category}</span>
                            {n.source_file && <span style={{ ...tagStyle, color: '#3B82F6' }}>{n.source_file}</span>}
                            <Toggle checked={n.is_active} onChange={(v) => handleSave({ ...n, is_active: v })} />
                            <button onClick={() => { setEditItem(n); setShowModal(true); }} style={iconBtn}><Edit2 size={14} /></button>
                            <button onClick={() => setDeleteId(n.id)} style={{ ...iconBtn, color: '#EF4444' }}><Trash2 size={14} /></button>
                        </div>
                    ))}
                </div>
            )}
            <NoteModal isOpen={showModal} onClose={() => { setShowModal(false); setEditItem(null); }} onSave={handleSave} initial={editItem} />
            <ConfirmDialog isOpen={!!deleteId} title="Hapus Catatan?" message="Catatan ini akan dihapus permanen." onConfirm={handleDelete} onCancel={() => setDeleteId(null)} />
        </div>
    );
}

function NoteModal({ isOpen, onClose, onSave, initial }) {
    const [title, setTitle] = useState('');
    const [content, setContent] = useState('');
    const [category, setCategory] = useState('umum');

    useEffect(() => {
        if (initial) { setTitle(initial.title); setContent(initial.content); setCategory(initial.category); }
        else { setTitle(''); setContent(''); setCategory('umum'); }
    }, [initial, isOpen]);

    return (
        <Modal isOpen={isOpen} onClose={onClose} title={initial ? 'Edit Catatan' : 'Tambah Catatan'}>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                <label style={lbl}>Judul<input value={title} onChange={e => setTitle(e.target.value)} style={inp} /></label>
                <label style={lbl}>Konten<textarea value={content} onChange={e => setContent(e.target.value)} rows={6} style={{ ...inp, resize: 'vertical' }} /></label>
                <label style={lbl}>Kategori<input value={category} onChange={e => setCategory(e.target.value)} style={inp} /></label>
                <button onClick={() => onSave({ id: initial?.id || 0, title, content, category, source_file: initial?.source_file || '', is_active: initial?.is_active ?? true, updated_at: '' })}
                    disabled={!title.trim() || !content.trim()} style={{ ...btnStyle, width: '100%', justifyContent: 'center' }}>Simpan</button>
            </div>
        </Modal>
    );
}

const btnStyle = { display: 'flex', alignItems: 'center', gap: 6, padding: '8px 16px', borderRadius: 8, border: 'none', background: '#3B82F6', color: '#fff', cursor: 'pointer', fontSize: '0.82rem', fontWeight: 600 };
const rowStyle = { display: 'flex', alignItems: 'center', gap: 12, padding: '12px 16px', borderRadius: 10, background: '#1E293B', border: '1px solid #334155' };
const iconBtn = { background: 'none', border: 'none', color: '#64748B', cursor: 'pointer', padding: 4 };
const tagStyle = { fontSize: '0.7rem', padding: '2px 8px', borderRadius: 6, background: '#0F172A', color: '#64748B', border: '1px solid #334155' };
const lbl = { display: 'flex', flexDirection: 'column', gap: 4, fontSize: '0.82rem', color: '#94A3B8', fontWeight: 500 };
const inp = { padding: '10px 12px', borderRadius: 8, border: '1px solid #334155', background: '#0F172A', color: '#E2E8F0', fontSize: '0.88rem', outline: 'none' };
