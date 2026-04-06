// Frontend status constants mirroring Go config/constants.go
export const STATUS_AI = 'ai';
export const STATUS_HANDOVER_PENDING = 'handover_pending';
export const STATUS_HUMAN = 'human';
export const STATUS_RESOLVED = 'resolved';

export const ROLE_CUSTOMER = 'customer';
export const ROLE_AI = 'ai';
export const ROLE_AGENT = 'agent';

export const STOCK_AVAILABLE = 'available';
export const STOCK_OUT_OF_STOCK = 'out_of_stock';
export const STOCK_PRE_ORDER = 'pre_order';

// Status display config
export const STATUS_COLORS = {
    [STATUS_AI]: '#22C55E',
    [STATUS_HANDOVER_PENDING]: '#F59E0B',
    [STATUS_HUMAN]: '#3B82F6',
    [STATUS_RESOLVED]: '#9CA3AF',
};

export const STATUS_LABELS = {
    [STATUS_AI]: 'AI Aktif',
    [STATUS_HANDOVER_PENDING]: 'Menunggu Agent',
    [STATUS_HUMAN]: 'Agent Handle',
    [STATUS_RESOLVED]: 'Selesai',
};
