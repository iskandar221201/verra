import { useState, useEffect, useRef } from 'react';
import { GetMessages } from '../../wailsjs/go/main/App';
import { useWailsEvent } from './useWailsEvent';

/**
 * Hook to manage messages for a conversation.
 */
export function useConversation(convId) {
    const [messages, setMessages] = useState([]);
    const [loading, setLoading] = useState(false);
    const bottomRef = useRef(null);

    // Load initial messages
    useEffect(() => {
        if (!convId) {
            setMessages([]);
            return;
        }
        setLoading(true);
        GetMessages(convId, 50)
            .then((msgs) => setMessages(msgs || []))
            .catch((err) => console.error('GetMessages:', err))
            .finally(() => setLoading(false));
    }, [convId]);

    // Subscribe to new messages
    useWailsEvent('verra:new_message', (msg) => {
        if (msg.conversation_id !== convId) return;
        setMessages((prev) => [...prev, msg]);
    });

    // Auto-scroll when messages change
    useEffect(() => {
        if (bottomRef.current) {
            bottomRef.current.scrollIntoView({ behavior: 'smooth' });
        }
    }, [messages]);

    return { messages, loading, bottomRef };
}
