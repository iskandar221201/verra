package whatsapp

import (
	"context"
	"fmt"
	"verra/internal/dto"

	_ "github.com/mattn/go-sqlite3"
	"github.com/wailsapp/wails/v2/pkg/runtime"
	"go.mau.fi/whatsmeow"
	"go.mau.fi/whatsmeow/store/sqlstore"
	waLog "go.mau.fi/whatsmeow/util/log"
)

type WhatsAppClient struct {
	Client *whatsmeow.Client
	ctx    context.Context
}

func NewWhatsAppClient(ctx context.Context) *WhatsAppClient {
	return &WhatsAppClient{
		ctx: ctx,
	}
}

func (w *WhatsAppClient) Init() error {
	dbLog := waLog.Stdout("Database", "DEBUG", true)
	container, err := sqlstore.New(context.Background(), "sqlite3", "file:verra_wa.db?_foreign_keys=on", dbLog)
	if err != nil {
		return fmt.Errorf("whatsapp.Init: failed to connect to session store: %w", err)
	}

	deviceRes, err := container.GetFirstDevice(context.Background())
	if err != nil {
		return fmt.Errorf("whatsapp.Init: failed to get device: %w", err)
	}

	clientLog := waLog.Stdout("WhatsApp", "DEBUG", true)
	w.Client = whatsmeow.NewClient(deviceRes, clientLog)

	// Register event handlers
	w.RegisterHandlers()

	if w.Client.Store.ID == nil {
		// New login
		qrChan, _ := w.Client.GetQRChannel(context.Background())
		err = w.Client.Connect()
		if err != nil {
			return fmt.Errorf("whatsapp.Init: failed to connect: %w", err)
		}
		go func() {
			for evt := range qrChan {
				if evt.Event == "code" {
					runtime.EventsEmit(w.ctx, "verra:qr_code", evt.Code)
				}
			}
		}()
	} else {
		// Already logged in, just connect
		err = w.Client.Connect()
		if err != nil {
			return fmt.Errorf("whatsapp.Init: failed to auto-connect: %w", err)
		}
	}

	return nil
}

func (w *WhatsAppClient) GetStatus() dto.WAStatus {
	if w.Client == nil {
		return dto.WAStatus{State: "disconnected"}
	}
	if w.Client.IsConnected() {
		return dto.WAStatus{
			State: "connected",
			Phone: w.Client.Store.ID.String(),
		}
	}
	return dto.WAStatus{State: "disconnected"}
}

func (w *WhatsAppClient) Disconnect() {
	if w.Client != nil {
		w.Client.Disconnect()
	}
}
