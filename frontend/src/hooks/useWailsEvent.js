import { useEffect, useCallback, useRef } from 'react';
import { EventsOn } from '../../wailsjs/runtime/runtime';

/**
 * Hook to subscribe to Wails events with automatic cleanup.
 */
export function useWailsEvent(eventName, callback) {
    const callbackRef = useRef(callback);
    callbackRef.current = callback;

    useEffect(() => {
        const handler = (...args) => callbackRef.current(...args);
        const unlisten = EventsOn(eventName, handler);
        return () => {
            if (unlisten) unlisten();
        };
    }, [eventName]);
}
