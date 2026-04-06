import { useEffect } from 'react';
import { GetInbox, GetWAStatus } from '../../wailsjs/go/main/App';
import { useWailsEvent } from './useWailsEvent';
import useAppStore from '../store/appStore';

/**
 * Hook to manage inbox state and WA event subscriptions.
 */
export function useInbox() {
    const setConversations = useAppStore((s) => s.setConversations);
    const updateConversation = useAppStore((s) => s.updateConversation);
    const setWAStatus = useAppStore((s) => s.setWAStatus);

    // Load initial data
    useEffect(() => {
        GetInbox()
            .then((convs) => setConversations(convs || []))
            .catch((err) => console.error('GetInbox:', err));

        GetWAStatus()
            .then((status) => setWAStatus(status.state))
            .catch((err) => console.error('GetWAStatus:', err));
    }, []);

    // Subscribe to inbox updates
    useWailsEvent('verra:inbox_update', (conv) => {
        updateConversation(conv);
    });

    // Subscribe to status changes
    useWailsEvent('verra:status_change', (data) => {
        updateConversation({ id: data.convID, status: data.status });
    });

    // Subscribe to WA status changes
    useWailsEvent('verra:wa_status', (state) => {
        setWAStatus(typeof state === 'string' ? state : state.state || 'disconnected');
    });
}
