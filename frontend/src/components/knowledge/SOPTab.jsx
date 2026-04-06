import React, { useState, useEffect } from 'react';
import { GetSOPs, SaveSOP, DeleteSOP } from '../../../wailsjs/go/main/App';
import Modal from '../ui/Modal';
import ConfirmDialog from '../ui/ConfirmDialog';
import Toggle from '../ui/Toggle';
import TagInput from '../ui/TagInput';
import EmptyState from '../ui/EmptyState';
import { Plus, Edit2, Trash2 } from 'lucide-react';
import toast from 'react-hot-toast';

export default function SOPTab() {
    const [sops, setSOPs] = useState([]);
    const [showModal, setShowModal] = useState(false);
    const [editItem, setEditItem] = useState(null);
    const [deleteId, setDeleteId] = useState(null);

    const loadData = () => GetSOPs().then(s => setSOPs(s || [])).catch(console.error);
    useEffect(() => { loadData(); }, []);

    const handleSave = async (sop) => {
        try { await SaveSOP(sop); toast.success(sop.id ? 'SOP diperbarui' : 'SOP ditambahkan'); setShowModal(false); setEditItem(null); loadData(); }
        catch (err) { toast.error('Gagal menyimpan SOP'); }
    };

    const handleDelete = async () => {
        try { await DeleteSOP(deleteId); toast.success('SOP dihapus'); setDeleteId(null); loadData(); }
        catch (err) { toast.error('Gagal menghapus'); }
    };

    return (
        <div>
            <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: 16 }}>
                <button onClick={() => { setEditItem(null); setShowModal(true); }} style={btnStyle}><Plus size={16} /> Tambah SOP</button>
            </div>
            {sops.length === 0 ? (
                <EmptyState icon="📋" title="Belum ada SOP" description="Tambahkan SOP agar AI mengikuti prosedur tertentu." />
            ) : (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                    {sops.map((s) => (
                        <div key={s.id} style={rowStyle}>
                            <div style={{ flex: 1 }}>
                                <div style={{ fontWeight: 600, color: '#E2E8F0', fontSize: '0.88rem' }}>{s.title}</div>
                                <div style={{ color: '#94A3B8', fontSize: '0.8rem' }}>
                                    {(s.trigger_keywords || []).join(', ')} • {(s.steps || []).length} langkah
                                    {s.escalate_to_human && <span style={{ color: '#F59E0B' }}> • Eskalasi ke manusia</span>}
                                </div>
                            </div>
                            <Toggle checked={s.is_active} onChange={(v) => handleSave({ ...s, is_active: v })} />
                            <button onClick={() => { setEditItem(s); setShowModal(true); }} style={iconBtn}><Edit2 size={14} /></button>
                            <button onClick={() => setDeleteId(s.id)} style={{ ...iconBtn, color: '#EF4444' }}><Trash2 size={14} /></button>
                        </div>
                    ))}
                </div>
            )}
            <SOPModal isOpen={showModal} onClose={() => { setShowModal(false); setEditItem(null); }} onSave={handleSave} initial={editItem} />
            <ConfirmDialog isOpen={!!deleteId} title="Hapus SOP?" message="SOP ini akan dihapus permanen." onConfirm={handleDelete} onCancel={() => setDeleteId(null)} />
        </div>
    );
}

function SOPModal({ isOpen, onClose, onSave, initial }) {
    const [title, setTitle] = useState('');
    const [keywords, setKeywords] = useState([]);
    const [steps, setSteps] = useState(['']);
    const [escalate, setEscalate] = useState(false);

    useEffect(() => {
        if (initial) { setTitle(initial.title); setKeywords(initial.trigger_keywords || []); setSteps(initial.steps?.length ? initial.steps : ['']); setEscalate(initial.escalate_to_human); }
        else { setTitle(''); setKeywords([]); setSteps(['']); setEscalate(false); }
    }, [initial, isOpen]);

    const updateStep = (i, val) => { const s = [...steps]; s[i] = val; setSteps(s); };
    const addStep = () => setSteps([...steps, '']);
    const removeStep = (i) => setSteps(steps.filter((_, j) => j !== i));

    return (
        <Modal isOpen={isOpen} onClose={onClose} title={initial ? 'Edit SOP' : 'Tambah SOP'} width={600}>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                <label style={lbl}>Judul SOP<input value={title} onChange={e => setTitle(e.target.value)} style={inp} /></label>
                <label style={lbl}>Trigger Keywords<TagInput tags={keywords} onChange={setKeywords} /></label>
                <div style={lbl}>
                    Langkah-langkah
                    {steps.map((step, i) => (
                        <div key={i} style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                            <span style={{ color: '#64748B', fontSize: '0.8rem', width: 20 }}>{i + 1}.</span>
                            <input value={step} onChange={e => updateStep(i, e.target.value)} style={{ ...inp, flex: 1 }} />
                            {steps.length > 1 && <button onClick={() => removeStep(i)} style={iconBtn}>✕</button>}
                        </div>
                    ))}
                    <button onClick={addStep} style={{ ...iconBtn, color: '#3B82F6', fontSize: '0.8rem' }}>+ Tambah langkah</button>
                </div>
                <Toggle checked={escalate} onChange={setEscalate} label="Eskalasi ke manusia" />
                <button onClick={() => onSave({ id: initial?.id || 0, title, trigger_keywords: keywords, steps: steps.filter(s => s.trim()), escalate_to_human: escalate, is_active: initial?.is_active ?? true })}
                    disabled={!title.trim()} style={{ ...btnStyle, width: '100%', justifyContent: 'center' }}>Simpan</button>
            </div>
        </Modal>
    );
}

const btnStyle = { display: 'flex', alignItems: 'center', gap: 6, padding: '8px 16px', borderRadius: 8, border: 'none', background: '#3B82F6', color: '#fff', cursor: 'pointer', fontSize: '0.82rem', fontWeight: 600 };
const rowStyle = { display: 'flex', alignItems: 'center', gap: 12, padding: '12px 16px', borderRadius: 10, background: '#1E293B', border: '1px solid #334155' };
const iconBtn = { background: 'none', border: 'none', color: '#64748B', cursor: 'pointer', padding: 4 };
const lbl = { display: 'flex', flexDirection: 'column', gap: 4, fontSize: '0.82rem', color: '#94A3B8', fontWeight: 500 };
const inp = { padding: '10px 12px', borderRadius: 8, border: '1px solid #334155', background: '#0F172A', color: '#E2E8F0', fontSize: '0.88rem', outline: 'none' };
