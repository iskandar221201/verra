import React, { useState, useEffect } from 'react';
import { GetAPIKeys, AddAPIKey, ToggleAPIKey, DeleteAPIKey } from '../../wailsjs/go/main/App';
import ConfirmDialog from '../components/ui/ConfirmDialog';
import Toggle from '../components/ui/Toggle';
import EmptyState from '../components/ui/EmptyState';
import { Key, Plus, Trash2, Clock, Zap } from 'lucide-react';
import { formatRelativeTime } from '../lib/format';
import toast from 'react-hot-toast';

export default function APIKeysPage() {
    const [keys, setKeys] = useState([]);
    const [showAdd, setShowAdd] = useState(false);
    const [label, setLabel] = useState('');
    const [apiKey, setApiKey] = useState('');
    const [deleteId, setDeleteId] = useState(null);
    const [adding, setAdding] = useState(false);

    const loadData = () => GetAPIKeys().then(k => setKeys(k || [])).catch(console.error);
    useEffect(() => { loadData(); }, []);

    const handleAdd = async () => {
        if (!label.trim() || !apiKey.trim()) return;
        setAdding(true);
        try { await AddAPIKey(label, apiKey); toast.success('API Key ditambahkan'); setShowAdd(false); setLabel(''); setApiKey(''); loadData(); }
        catch (err) { toast.error('Gagal menambahkan API Key'); }
        finally { setAdding(false); }
    };

    const handleToggle = async (id, active) => {
        try { await ToggleAPIKey(id, active); loadData(); }
        catch (err) { toast.error('Gagal mengubah status'); }
    };

    const handleDelete = async () => {
        try { await DeleteAPIKey(deleteId); toast.success('API Key dihapus'); setDeleteId(null); loadData(); }
        catch (err) { toast.error('Gagal menghapus'); }
    };

    return (
        <div style={{ flex: 1, overflowY: 'auto', padding: '24px 32px', maxWidth: 700 }}>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 24 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                    <Key size={22} color="#3B82F6" />
                    <h2 style={{ margin: 0, fontSize: '1.2rem', color: '#E2E8F0', fontWeight: 700 }}>Gemini API Keys</h2>
                </div>
                <button onClick={() => setShowAdd(!showAdd)} style={btnStyle}>
                    <Plus size={16} /> Tambah Key
                </button>
            </div>

            {/* Add Key Form */}
            {showAdd && (
                <div style={{ ...cardStyle, marginBottom: 16 }}>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                        <label style={lbl}>Label<input value={label} onChange={e => setLabel(e.target.value)} placeholder="Contoh: Key Utama" style={inp} /></label>
                        <label style={lbl}>API Key<input value={apiKey} onChange={e => setApiKey(e.target.value)} placeholder="AIza..." type="password" style={inp} /></label>
                        <div style={{ display: 'flex', gap: 8, justifyContent: 'flex-end' }}>
                            <button onClick={() => setShowAdd(false)} style={{ ...btnOutline }}>Batal</button>
                            <button onClick={handleAdd} disabled={adding || !label.trim() || !apiKey.trim()} style={{ ...btnStyle, opacity: adding ? 0.5 : 1 }}>
                                {adding ? 'Menyimpan...' : 'Simpan'}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Key List */}
            {keys.length === 0 ? (
                <EmptyState icon="🔑" title="Belum ada API Key" description="Tambahkan Gemini API Key untuk mengaktifkan AI." />
            ) : (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                    {keys.map((k) => (
                        <div key={k.id} style={cardStyle}>
                            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                                <div>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 4 }}>
                                        <span style={{ fontWeight: 600, color: '#E2E8F0', fontSize: '0.9rem' }}>{k.label}</span>
                                        {k.in_cooldown && <span style={{ fontSize: '0.65rem', padding: '1px 6px', borderRadius: 4, background: '#F59E0B20', color: '#F59E0B' }}>Cooldown</span>}
                                    </div>
                                    <div style={{ fontFamily: 'monospace', fontSize: '0.8rem', color: '#64748B' }}>{k.masked_key}</div>
                                    <div style={{ display: 'flex', gap: 16, marginTop: 6 }}>
                                        <span style={{ display: 'flex', alignItems: 'center', gap: 4, fontSize: '0.75rem', color: '#475569' }}>
                                            <Zap size={12} /> {k.total_requests} requests
                                        </span>
                                        {k.last_used_at && (
                                            <span style={{ display: 'flex', alignItems: 'center', gap: 4, fontSize: '0.75rem', color: '#475569' }}>
                                                <Clock size={12} /> {formatRelativeTime(k.last_used_at)}
                                            </span>
                                        )}
                                    </div>
                                </div>
                                <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                                    <Toggle checked={k.is_active} onChange={(v) => handleToggle(k.id, v)} />
                                    <button onClick={() => setDeleteId(k.id)} style={{ background: 'none', border: 'none', color: '#EF4444', cursor: 'pointer', padding: 4 }}>
                                        <Trash2 size={16} />
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            <ConfirmDialog isOpen={!!deleteId} title="Hapus API Key?" message="API Key ini akan dihapus permanen." onConfirm={handleDelete} onCancel={() => setDeleteId(null)} />
        </div>
    );
}

const btnStyle = { display: 'flex', alignItems: 'center', gap: 6, padding: '8px 16px', borderRadius: 8, border: 'none', background: '#3B82F6', color: '#fff', cursor: 'pointer', fontSize: '0.82rem', fontWeight: 600 };
const btnOutline = { padding: '8px 16px', borderRadius: 8, border: '1px solid #475569', background: 'transparent', color: '#CBD5E1', cursor: 'pointer', fontSize: '0.82rem' };
const cardStyle = { background: '#1E293B', borderRadius: 12, padding: '16px', border: '1px solid #334155' };
const lbl = { display: 'flex', flexDirection: 'column', gap: 4, fontSize: '0.82rem', color: '#94A3B8', fontWeight: 500 };
const inp = { padding: '10px 12px', borderRadius: 8, border: '1px solid #334155', background: '#0F172A', color: '#E2E8F0', fontSize: '0.88rem', outline: 'none' };
