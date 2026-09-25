import QRCode from 'qrcode';

const canvas = document.getElementById('two-factor-qr');
const errorMessage = document.getElementById('two-factor-qr-error');

if (canvas) {
    QRCode.toCanvas(canvas, canvas.dataset.qrUri, {
        width: 220,
        margin: 2,
        errorCorrectionLevel: 'M',
        color: {
            dark: '#172033',
            light: '#ffffff',
        },
    }).catch(() => {
        canvas.hidden = true;

        if (errorMessage) {
            errorMessage.hidden = false;
        }
    });
}
