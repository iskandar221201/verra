import { create } from 'zustand';

const useAppStore = create((set) => ({
    // Navigation
    activePage: 'inbox',
    setActivePage: (page) => set({ activePage: page }),

    // Inbox
    conversations: [],
    selectedConvId: null,
    setConversations: (convs) => set({ conversations: convs }),
    setSelectedConvId: (id) => set({ selectedConvId: id }),
    updateConversation: (conv) => set((state) => ({
        conversations: state.conversations.some(c => c.id === conv.id)
            ? state.conversations.map(c => c.id === conv.id ? { ...c, ...conv } : c)
            : [conv, ...state.conversations]
    })),

    // WA Status
    waStatus: 'disconnected',
    setWAStatus: (status) => set({ waStatus: status }),
}));

export default useAppStore;
