package whatsapp

import (
	"fmt"

	"github.com/wailsapp/wails/v2/pkg/runtime"
	"go.mau.fi/whatsmeow/types/events"
)

func (w *WhatsAppClient) RegisterHandlers() {
	w.Client.AddEventHandler(func(evt interface{}) {
		switch v := evt.(type) {
		case *events.Message:
			w.handleIncomingMessage(v)
		case *events.Connected:
			runtime.EventsEmit(w.ctx, "verra:wa_status", "connected")
		case *events.LoggedOut:
			runtime.EventsEmit(w.ctx, "verra:wa_status", "disconnected")
		}
	})
}

func (w *WhatsAppClient) handleIncomingMessage(evt *events.Message) {
	// Only handle texts for now
	text := evt.Message.GetConversation()
	if text == "" {
		text = evt.Message.GetExtendedTextMessage().GetText()
	}

	if text != "" {
		fmt.Printf("Received message from %s: %s\n", evt.Info.Sender.String(), text)

		// Map whatsmeow event to our DTO/Internal event if needed
		// runtime.EventsEmit(w.ctx, "verra:new_message", map[string]interface{}{
		//     "sender": evt.Info.Sender.String(),
		//     "text":   text,
		// })
	}
}
