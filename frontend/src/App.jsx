import React from 'react';
import { Toaster } from 'react-hot-toast';
import Sidebar from './components/layout/Sidebar';
import InboxPage from './pages/InboxPage';
import KnowledgePage from './pages/KnowledgePage';
import SettingsPage from './pages/SettingsPage';
import APIKeysPage from './pages/APIKeysPage';
import { useInbox } from './hooks/useInbox';
import useAppStore from './store/appStore';
import './App.css';

function App() {
    const activePage = useAppStore((s) => s.activePage);

    // Initialize inbox data and event subscriptions
    useInbox();

    const renderPage = () => {
        switch (activePage) {
            case 'inbox': return <InboxPage />;
            case 'knowledge': return <KnowledgePage />;
            case 'settings': return <SettingsPage />;
            case 'apikeys': return <APIKeysPage />;
            default: return <InboxPage />;
        }
    };

    return (
        <div id="App" style={{
            display: 'flex', height: '100vh', width: '100vw',
            background: '#0B1121', color: '#E2E8F0',
            fontFamily: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
            overflow: 'hidden',
        }}>
            <Sidebar />
            <main style={{ flex: 1, display: 'flex', flexDirection: 'column', overflow: 'hidden' }}>
                {renderPage()}
            </main>
            <Toaster
                position="top-right"
                toastOptions={{
                    style: {
                        background: '#1E293B',
                        color: '#E2E8F0',
                        border: '1px solid #334155',
                        borderRadius: '10px',
                        fontSize: '0.85rem',
                    },
                }}
            />
        </div>
    );
}

export default App;
