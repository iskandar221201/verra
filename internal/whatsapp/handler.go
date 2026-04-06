package whatsapp

import (
	"fmt"

	"github.com/wailsapp/wails/v2/pkg/runtime"
	"go.mau.fi/whatsmeow/types/events"
)

// RegisterHandlers sets up the whatsmeow event handler.
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

// handleIncomingMessage processes incoming WhatsApp messages.
func (w *WhatsAppClient) handleIncomingMessage(evt *events.Message) {
	// Skip messages from self
	if evt.Info.IsFromMe {
		return
	}

	// Extract text content
	text := evt.Message.GetConversation()
	if text == "" {
		text = evt.Message.GetExtendedTextMessage().GetText()
	}

	if text != "" {
		senderJID := evt.Info.Sender.String()
		pushName := evt.Info.PushName
		msgID := evt.Info.ID

		fmt.Printf("Received message from %s (%s): %s\n", pushName, senderJID, text)

		// Delegate to the conversation service handler if set
		if w.messageHandler != nil {
			w.messageHandler(senderJID, pushName, msgID, text)
		}
	}
}
