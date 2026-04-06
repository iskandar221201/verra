package whatsapp

import (
	"context"
	"fmt"
	"verra/internal/dto"

	"github.com/wailsapp/wails/v2/pkg/runtime"
	"go.mau.fi/whatsmeow"
	"go.mau.fi/whatsmeow/store/sqlstore"
	waLog "go.mau.fi/whatsmeow/util/log"
	_ "modernc.org/sqlite"
)

// MessageHandler is a callback for processing incoming messages.
type MessageHandler func(senderJID, pushName, msgID, text string)

// WhatsAppClient wraps the whatsmeow client.
type WhatsAppClient struct {
	Client         *whatsmeow.Client
	ctx            context.Context
	messageHandler MessageHandler
	qrCode         string
}

// NewWhatsAppClient creates a new WhatsAppClient.
func NewWhatsAppClient(ctx context.Context) *WhatsAppClient {
	return &WhatsAppClient{
		ctx: ctx,
	}
}

// SetMessageHandler sets the callback for incoming messages.
func (w *WhatsAppClient) SetMessageHandler(handler MessageHandler) {
	w.messageHandler = handler
}

// Init initializes the WhatsApp client and connects.
func (w *WhatsAppClient) Init() (err error) {
	defer func() {
		if r := recover(); r != nil {
			fmt.Printf("whatsapp.Init: panicked: %v\n", r)
			err = fmt.Errorf("whatsapp.Init: panicked: %v", r)
		}
	}()

	fmt.Println("whatsapp.Init: Starting WhatsApp initialization...")

	dbLog := waLog.Stdout("Database", "DEBUG", true)
	container, err := sqlstore.New(context.Background(), "sqlite", "file:verra_wa.db?_pragma=foreign_keys(1)", dbLog)
	if err != nil {
		fmt.Printf("whatsapp.Init: failed to connect to session store: %v\n", err)
		return fmt.Errorf("whatsapp.Init: failed to connect to session store: %w", err)
	}

	deviceRes, err := container.GetFirstDevice(context.Background())
	if err != nil {
		fmt.Printf("whatsapp.Init: failed to get device: %v\n", err)
		return fmt.Errorf("whatsapp.Init: failed to get device: %w", err)
	}

	clientLog := waLog.Stdout("WhatsApp", "DEBUG", true)
	w.Client = whatsmeow.NewClient(deviceRes, clientLog)
	if w.Client == nil {
		return fmt.Errorf("whatsapp.Init: failed to create whatsmeow client")
	}

	// Register event handlers
	w.RegisterHandlers()

	if w.Client.Store.ID == nil {
		// New login
		fmt.Println("whatsapp.Init: No session found, requesting QR code...")
		qrChan, _ := w.Client.GetQRChannel(context.Background())
		err = w.Client.Connect()
		if err != nil {
			fmt.Printf("whatsapp.Init: failed to connect: %v\n", err)
			return fmt.Errorf("whatsapp.Init: failed to connect: %w", err)
		}
		go func() {
			for evt := range qrChan {
				switch evt.Event {
				case "code":
					fmt.Printf("whatsapp.Init: QR code received: %s\n", evt.Code)
					w.qrCode = evt.Code
					runtime.EventsEmit(w.ctx, "verra:qr_code", evt.Code)
				case "success":
					fmt.Println("whatsapp.Init: QR login success")
					w.qrCode = ""
					runtime.EventsEmit(w.ctx, "verra:wa_status", "connected")
				}
			}
		}()
	} else {
		// Already logged in, just connect
		fmt.Println("whatsapp.Init: Existing session found, connecting...")
		err = w.Client.Connect()
		if err != nil {
			fmt.Printf("whatsapp.Init: failed to auto-connect: %v\n", err)
			return fmt.Errorf("whatsapp.Init: failed to auto-connect: %w", err)
		}
		runtime.EventsEmit(w.ctx, "verra:wa_status", "connected")
	}

	fmt.Println("whatsapp.Init: WhatsApp initialization completed successfully")
	w.qrCode = ""
	return nil
}

// GetStatus returns the current WhatsApp connection status.
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

// GetQRCode returns the current QR code if available.
func (w *WhatsAppClient) GetQRCode() string {
	return w.qrCode
}

// Disconnect disconnects the WhatsApp client.
func (w *WhatsAppClient) Disconnect() {
	if w.Client != nil {
		w.Client.Disconnect()
	}
}
