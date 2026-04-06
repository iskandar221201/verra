import React, { useState, useEffect } from 'react';
import { GetBusinessConfig, SaveBusinessConfig, GetQRCode, InitWA, DisconnectWA } from '../../wailsjs/go/main/App';
import TagInput from '../components/ui/TagInput';
import { Settings as SettingsIcon, Wifi, WifiOff, QrCode } from 'lucide-react';
import useAppStore from '../store/appStore';
import { useWailsEvent } from '../hooks/useWailsEvent';
import toast from 'react-hot-toast';

export default function SettingsPage() {
    const [config, setConfig] = useState(null);
    const [saving, setSaving] = useState(false);
    const [qrCode, setQrCode] = useState('');
    const waStatus = useAppStore((s) => s.waStatus);

    useEffect(() => {
        GetBusinessConfig().then(setConfig).catch(console.error);
        GetQRCode().then(code => {
            if (code) setQrCode(code);
        }).catch(console.error);
    }, []);

    useWailsEvent('verra:qr_code', (code) => setQrCode(code));

    const handleSave = async () => {
        setSaving(true);
        try { await SaveBusinessConfig(config); toast.success('Konfigurasi disimpan'); }
        catch (err) { toast.error('Gagal menyimpan'); }
        finally { setSaving(false); }
    };

    if (!config) return <div style={{ padding: 40, color: '#64748B' }}>Loading...</div>;

    return (
        <div style={{ flex: 1, overflowY: 'auto', padding: '24px 32px', maxWidth: 700 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 24 }}>
                <SettingsIcon size={22} color="#3B82F6" />
                <h2 style={{ margin: 0, fontSize: '1.2rem', color: '#E2E8F0', fontWeight: 700 }}>Pengaturan Bisnis</h2>
            </div>

            {/* WA Connection Status */}
            <div style={{ ...cardStyle, marginBottom: 24 }}>
                <h3 style={sectionTitle}>Koneksi WhatsApp</h3>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                    {waStatus === 'connected' ? <Wifi color="#22C55E" size={20} /> : <WifiOff color="#EF4444" size={20} />}
                    <span style={{ color: waStatus === 'connected' ? '#22C55E' : '#EF4444', fontWeight: 600 }}>
                        {waStatus === 'connected' ? 'Terhubung' : 'Tidak Terhubung'}
                    </span>
                    <div style={{ marginLeft: 'auto' }}>
                        {waStatus === 'connected' ? (
                            <button onClick={() => { DisconnectWA(); setQrCode(''); }} style={{ ...btnStyle, background: '#EF4444', padding: '6px 12px', fontSize: '0.8rem' }}>
                                Putuskan Koneksi
                            </button>
                        ) : (
                            <button onClick={() => { setQrCode(''); InitWA(); }} style={{ ...btnStyle, padding: '6px 12px', fontSize: '0.8rem' }}>
                                Refresh / Tampilkan QR
                            </button>
                        )}
                    </div>
                </div>
                {qrCode && waStatus !== 'connected' && (
                    <div style={{ marginTop: 16, textAlign: 'center' }}>
                        <p style={{ color: '#94A3B8', fontSize: '0.85rem', marginBottom: 12 }}>Scan QR code ini dengan WhatsApp:</p>
                        <div style={{ background: '#fff', padding: 16, borderRadius: 12, display: 'inline-block' }}>
                            <img src={`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qrCode)}`} alt="QR Code" width={200} height={200} />
                        </div>
                    </div>
                )}
            </div>

            {/* Business Config Form */}
            <div style={cardStyle}>
                <h3 style={sectionTitle}>Konfigurasi Bisnis</h3>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
                    <Field label="Nama Bisnis" value={config.business_name} onChange={(v) => setConfig({ ...config, business_name: v })} />
                    <Field label="Persona AI" value={config.ai_persona} onChange={(v) => setConfig({ ...config, ai_persona: v })} multiline />
                    <Field label="Bahasa" value={config.language} onChange={(v) => setConfig({ ...config, language: v })} />
                    <Field label="Context Window (N pesan)" type="number" value={config.context_window_n} onChange={(v) => setConfig({ ...config, context_window_n: parseInt(v) || 10 })} />

                    <div style={lbl}>
                        <span>Handover Keywords</span>
                        <TagInput tags={config.handover_keywords || []} onChange={(kw) => setConfig({ ...config, handover_keywords: kw })} />
                    </div>

                    <Field label="Pesan Greeting" value={config.greeting_message} onChange={(v) => setConfig({ ...config, greeting_message: v })} multiline />
                    <Field label="Pesan Handover" value={config.handover_message} onChange={(v) => setConfig({ ...config, handover_message: v })} multiline />
                    <Field label="Pesan Tunggu Handover" value={config.handover_wait_message} onChange={(v) => setConfig({ ...config, handover_wait_message: v })} multiline />

                    <button onClick={handleSave} disabled={saving} style={{ ...btnStyle, opacity: saving ? 0.5 : 1 }}>
                        {saving ? 'Menyimpan...' : 'Simpan Perubahan'}
                    </button>
                </div>
            </div>
        </div>
    );
}

function Field({ label, value, onChange, multiline, type = 'text' }) {
    const El = multiline ? 'textarea' : 'input';
    return (
        <label style={lbl}>
            <span>{label}</span>
            <El type={type} value={value} onChange={e => onChange(e.target.value)} rows={multiline ? 3 : undefined} style={{ ...inp, ...(multiline ? { resize: 'vertical' } : {}) }} />
        </label>
    );
}

const cardStyle = { background: '#1E293B', borderRadius: 12, padding: '20px', border: '1px solid #334155' };
const sectionTitle = { margin: '0 0 16px', fontSize: '0.95rem', color: '#E2E8F0', fontWeight: 600 };
const lbl = { display: 'flex', flexDirection: 'column', gap: 4, fontSize: '0.82rem', color: '#94A3B8', fontWeight: 500 };
const inp = { padding: '10px 12px', borderRadius: 8, border: '1px solid #334155', background: '#0F172A', color: '#E2E8F0', fontSize: '0.88rem', outline: 'none' };
const btnStyle = { display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6, padding: '10px 20px', borderRadius: 8, border: 'none', background: '#3B82F6', color: '#fff', cursor: 'pointer', fontSize: '0.88rem', fontWeight: 600 };
