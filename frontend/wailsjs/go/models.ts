export namespace dto {
	
	export class APIKeySafe {
	    id: number;
	    label: string;
	    masked_key: string;
	    is_active: boolean;
	    last_used_at: string;
	    total_requests: number;
	    in_cooldown: boolean;
	
	    static createFrom(source: any = {}) {
	        return new APIKeySafe(source);
	    }
	
	    constructor(source: any = {}) {
	        if ('string' === typeof source) source = JSON.parse(source);
	        this.id = source["id"];
	        this.label = source["label"];
	        this.masked_key = source["masked_key"];
	        this.is_active = source["is_active"];
	        this.last_used_at = source["last_used_at"];
	        this.total_requests = source["total_requests"];
	        this.in_cooldown = source["in_cooldown"];
	    }
	}
	export class BusinessConfig {
	    business_name: string;
	    ai_persona: string;
	    language: string;
	    context_window_n: number;
	    handover_keywords: string[];
	    greeting_message: string;
	    handover_message: string;
	    handover_wait_message: string;
	
	    static createFrom(source: any = {}) {
	        return new BusinessConfig(source);
	    }
	
	    constructor(source: any = {}) {
	        if ('string' === typeof source) source = JSON.parse(source);
	        this.business_name = source["business_name"];
	        this.ai_persona = source["ai_persona"];
	        this.language = source["language"];
	        this.context_window_n = source["context_window_n"];
	        this.handover_keywords = source["handover_keywords"];
	        this.greeting_message = source["greeting_message"];
	        this.handover_message = source["handover_message"];
	        this.handover_wait_message = source["handover_wait_message"];
	    }
	}
	export class ConversationSummary {
	    id: string;
	    customer_name: string;
	    last_message: string;
	    last_message_at: string;
	    status: string;
	    unread_count: number;
	
	    static createFrom(source: any = {}) {
	        return new ConversationSummary(source);
	    }
	
	    constructor(source: any = {}) {
	        if ('string' === typeof source) source = JSON.parse(source);
	        this.id = source["id"];
	        this.customer_name = source["customer_name"];
	        this.last_message = source["last_message"];
	        this.last_message_at = source["last_message_at"];
	        this.status = source["status"];
	        this.unread_count = source["unread_count"];
	    }
	}
	export class FAQ {
	    id: number;
	    question: string;
	    answer: string;
	    category: string;
	    sort_order: number;
	    is_active: boolean;
	
	    static createFrom(source: any = {}) {
	        return new FAQ(source);
	    }
	
	    constructor(source: any = {}) {
	        if ('string' === typeof source) source = JSON.parse(source);
	        this.id = source["id"];
	        this.question = source["question"];
	        this.answer = source["answer"];
	        this.category = source["category"];
	        this.sort_order = source["sort_order"];
	        this.is_active = source["is_active"];
	    }
	}
	export class Message {
	    id: string;
	    conversation_id: string;
	    role: string;
	    content: string;
	    created_at: string;
	
	    static createFrom(source: any = {}) {
	        return new Message(source);
	    }
	
	    constructor(source: any = {}) {
	        if ('string' === typeof source) source = JSON.parse(source);
	        this.id = source["id"];
	        this.conversation_id = source["conversation_id"];
	        this.role = source["role"];
	        this.content = source["content"];
	        this.created_at = source["created_at"];
	    }
	}
	export class Note {
	    id: number;
	    title: string;
	    content: string;
	    category: string;
	    source_file: string;
	    is_active: boolean;
	    updated_at: string;
	
	    static createFrom(source: any = {}) {
	        return new Note(source);
	    }
	
	    constructor(source: any = {}) {
	        if ('string' === typeof source) source = JSON.parse(source);
	        this.id = source["id"];
	        this.title = source["title"];
	        this.content = source["content"];
	        this.category = source["category"];
	        this.source_file = source["source_file"];
	        this.is_active = source["is_active"];
	        this.updated_at = source["updated_at"];
	    }
	}
	export class Product {
	    id: number;
	    name: string;
	    price: number;
	    description: string;
	    stock_status: string;
	    category: string;
	    is_active: boolean;
	
	    static createFrom(source: any = {}) {
	        return new Product(source);
	    }
	
	    constructor(source: any = {}) {
	        if ('string' === typeof source) source = JSON.parse(source);
	        this.id = source["id"];
	        this.name = source["name"];
	        this.price = source["price"];
	        this.description = source["description"];
	        this.stock_status = source["stock_status"];
	        this.category = source["category"];
	        this.is_active = source["is_active"];
	    }
	}
	export class SOP {
	    id: number;
	    title: string;
	    trigger_keywords: string[];
	    steps: string[];
	    escalate_to_human: boolean;
	    is_active: boolean;
	
	    static createFrom(source: any = {}) {
	        return new SOP(source);
	    }
	
	    constructor(source: any = {}) {
	        if ('string' === typeof source) source = JSON.parse(source);
	        this.id = source["id"];
	        this.title = source["title"];
	        this.trigger_keywords = source["trigger_keywords"];
	        this.steps = source["steps"];
	        this.escalate_to_human = source["escalate_to_human"];
	        this.is_active = source["is_active"];
	    }
	}
	export class WAStatus {
	    state: string;
	    phone: string;
	
	    static createFrom(source: any = {}) {
	        return new WAStatus(source);
	    }
	
	    constructor(source: any = {}) {
	        if ('string' === typeof source) source = JSON.parse(source);
	        this.state = source["state"];
	        this.phone = source["phone"];
	    }
	}

}

