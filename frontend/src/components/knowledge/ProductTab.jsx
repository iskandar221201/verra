import React, { useState, useEffect } from 'react';
import { GetProducts, SaveProduct, DeleteProduct } from '../../../wailsjs/go/main/App';
import Modal from '../ui/Modal';
import ConfirmDialog from '../ui/ConfirmDialog';
import Toggle from '../ui/Toggle';
import EmptyState from '../ui/EmptyState';
import { Plus, Edit2, Trash2 } from 'lucide-react';
import { formatRupiah } from '../../lib/format';
import toast from 'react-hot-toast';

export default function ProductTab() {
    const [products, setProducts] = useState([]);
    const [showModal, setShowModal] = useState(false);
    const [editItem, setEditItem] = useState(null);
    const [deleteId, setDeleteId] = useState(null);

    const loadData = () => GetProducts().then(p => setProducts(p || [])).catch(console.error);
    useEffect(() => { loadData(); }, []);

    const handleSave = async (product) => {
        try {
            await SaveProduct(product);
            toast.success(product.id ? 'Produk diperbarui' : 'Produk ditambahkan');
            setShowModal(false); setEditItem(null); loadData();
        } catch (err) { toast.error('Gagal menyimpan produk'); }
    };

    const handleDelete = async () => {
        try { await DeleteProduct(deleteId); toast.success('Produk dihapus'); setDeleteId(null); loadData(); }
        catch (err) { toast.error('Gagal menghapus'); }
    };

    const stockLabel = { available: 'Tersedia', out_of_stock: 'Habis', pre_order: 'Pre-order' };

    return (
        <div>
            <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: 16 }}>
                <button onClick={() => { setEditItem(null); setShowModal(true); }} style={btnStyle}><Plus size={16} /> Tambah Produk</button>
            </div>
            {products.length === 0 ? (
                <EmptyState icon="📦" title="Belum ada Produk" description="Tambahkan produk agar AI bisa memberikan info produk." />
            ) : (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                    {products.map((p) => (
                        <div key={p.id} style={rowStyle}>
                            <div style={{ flex: 1 }}>
                                <div style={{ fontWeight: 600, color: '#E2E8F0', fontSize: '0.88rem' }}>{p.name}</div>
                                <div style={{ color: '#94A3B8', fontSize: '0.8rem' }}>{formatRupiah(p.price)} • {stockLabel[p.stock_status] || p.stock_status}</div>
                            </div>
                            <span style={tagStyle}>{p.category}</span>
                            <Toggle checked={p.is_active} onChange={(v) => handleSave({ ...p, is_active: v })} />
                            <button onClick={() => { setEditItem(p); setShowModal(true); }} style={iconBtn}><Edit2 size={14} /></button>
                            <button onClick={() => setDeleteId(p.id)} style={{ ...iconBtn, color: '#EF4444' }}><Trash2 size={14} /></button>
                        </div>
                    ))}
                </div>
            )}
            <ProductModal isOpen={showModal} onClose={() => { setShowModal(false); setEditItem(null); }} onSave={handleSave} initial={editItem} />
            <ConfirmDialog isOpen={!!deleteId} title="Hapus Produk?" message="Produk ini akan dihapus permanen." onConfirm={handleDelete} onCancel={() => setDeleteId(null)} />
        </div>
    );
}

function ProductModal({ isOpen, onClose, onSave, initial }) {
    const [name, setName] = useState('');
    const [price, setPrice] = useState(0);
    const [description, setDescription] = useState('');
    const [stockStatus, setStockStatus] = useState('available');
    const [category, setCategory] = useState('umum');

    useEffect(() => {
        if (initial) { setName(initial.name); setPrice(initial.price); setDescription(initial.description); setStockStatus(initial.stock_status); setCategory(initial.category); }
        else { setName(''); setPrice(0); setDescription(''); setStockStatus('available'); setCategory('umum'); }
    }, [initial, isOpen]);

    return (
        <Modal isOpen={isOpen} onClose={onClose} title={initial ? 'Edit Produk' : 'Tambah Produk'}>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                <label style={lbl}>Nama Produk<input value={name} onChange={e => setName(e.target.value)} style={inp} /></label>
                <label style={lbl}>Harga (Rp)<input type="number" value={price} onChange={e => setPrice(parseInt(e.target.value) || 0)} style={inp} /></label>
                <label style={lbl}>Deskripsi<textarea value={description} onChange={e => setDescription(e.target.value)} rows={3} style={{ ...inp, resize: 'vertical' }} /></label>
                <label style={lbl}>Status Stok
                    <select value={stockStatus} onChange={e => setStockStatus(e.target.value)} style={inp}>
                        <option value="available">Tersedia</option><option value="out_of_stock">Habis</option><option value="pre_order">Pre-order</option>
                    </select>
                </label>
                <label style={lbl}>Kategori<input value={category} onChange={e => setCategory(e.target.value)} style={inp} /></label>
                <button onClick={() => onSave({ id: initial?.id || 0, name, price, description, stock_status: stockStatus, category, is_active: initial?.is_active ?? true })}
                    disabled={!name.trim()} style={{ ...btnStyle, width: '100%', justifyContent: 'center' }}>Simpan</button>
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
