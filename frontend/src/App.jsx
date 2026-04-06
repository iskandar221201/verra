import { useState, useEffect } from 'react';
import logo from './assets/images/logo-universal.png';
import './App.css';
import { GetWAStatus } from "../wailsjs/go/main/App";
import { EventsOn } from "../wailsjs/runtime/runtime";

function App() {
    const [status, setStatus] = useState("disconnected");

    useEffect(() => {
        GetWAStatus().then(res => setStatus(res.state));

        const unlisten = EventsOn("verra:wa_status", (s) => {
            setStatus(s);
        });

        return () => unlisten();
    }, []);

    return (
        <div id="App">
            <img src={logo} id="logo" alt="logo" />
            <div id="result" className="result">
                WhatsApp Status: <span style={{ color: status === 'connected' ? '#22C55E' : '#F59E0B' }}>{status}</span>
            </div>
            <div className="input-box">
                <p>Verra AI Project Initialized</p>
            </div>
        </div>
    )
}

export default App
