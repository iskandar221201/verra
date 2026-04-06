package whatsapp

import (
	"context"
	"fmt"

	"go.mau.fi/whatsmeow/proto/waE2E"
	"go.mau.fi/whatsmeow/types"
	"google.golang.org/protobuf/proto"
)

func (w *WhatsAppClient) SendText(jid string, text string) error {
	recipient, err := types.ParseJID(jid)
	if err != nil {
		return fmt.Errorf("whatsapp.SendText: invalid JID: %w", err)
	}

	msg := &waE2E.Message{
		Conversation: proto.String(text),
	}

	_, err = w.Client.SendMessage(context.Background(), recipient, msg)
	if err != nil {
		return fmt.Errorf("whatsapp.SendText: failed to send message: %w", err)
	}

	return nil
}
