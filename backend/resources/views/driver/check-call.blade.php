<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Check Call</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; margin: 0; padding: 0; background: #0f172a; color: #e2e8f0; }
        .shell { max-width: 640px; margin: 32px auto; padding: 24px; background: #111827; border: 1px solid #1f2937; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,0.4); }
        h1 { margin-top: 0; font-size: 24px; color: #f8fafc; }
        label { display: block; margin-top: 12px; font-size: 14px; color: #cbd5e1; }
        input, select, textarea { width: 100%; margin-top: 6px; padding: 10px 12px; border-radius: 8px; border: 1px solid #1f2937; background: #0b1224; color: #e2e8f0; }
        textarea { resize: vertical; min-height: 90px; }
        button { margin-top: 18px; width: 100%; padding: 12px 16px; border: none; border-radius: 10px; background: linear-gradient(135deg, #06b6d4, #2563eb); color: #f8fafc; font-weight: 700; cursor: pointer; }
        .muted { font-size: 12px; color: #94a3b8; margin-top: 8px; }
        .alert { padding: 10px 12px; border-radius: 10px; margin-bottom: 12px; }
        .alert.success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.4); color: #bbf7d0; }
        .alert.error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.4); color: #fecdd3; }
    </style>
</head>
<body>
    <div class="shell">
        <h1>Driver Check Call</h1>

        <div id="alert" class="alert" style="display:none;"></div>

        <form id="checkcall-form" enctype="multipart/form-data">
            <label>Load #
                <input type="text" name="load_number" placeholder="e.g., HA-12345" required>
            </label>

            <label>Stop ID (optional)
                <input type="number" name="stop_id" placeholder="If provided by dispatcher">
            </label>

            <label>Status
                <select name="status" required>
                    <option value="">Select status</option>
                    <option value="arrived_pickup">Arrived Pickup</option>
                    <option value="loaded">Loaded</option>
                    <option value="arrived_delivery">Arrived Delivery</option>
                    <option value="unloaded">Unloaded</option>
                    <option value="issue">Issue / Delay</option>
                    <option value="other">Other</option>
                </select>
            </label>

            <label>Note (optional)
                <textarea name="note" placeholder="Brief update for dispatch"></textarea>
            </label>

            <label>Photo / PDF (optional)
                <input type="file" name="document" accept="image/*,application/pdf">
            </label>

            <p class="muted">This form sends your update directly to dispatch and attaches to the load record.</p>
            <button type="submit">Send Update</button>
        </form>
    </div>

    <script>
        const form = document.getElementById('checkcall-form');
        const alertBox = document.getElementById('alert');

        function showAlert(message, type='success') {
            alertBox.textContent = message;
            alertBox.className = 'alert ' + type;
            alertBox.style.display = 'block';
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            alertBox.style.display = 'none';

            const data = new FormData(form);
            const token = document.getElementById('driver_token').value.trim();
            try {
                const res = await fetch('/api/driver/check-calls', {
                    method: 'POST',
                    body: data,
                    headers: {
                        'X-Driver-Token': token,
                    },
                });
                if (!res.ok) {
                    const text = await res.text();
                    throw new Error(text || 'Request failed');
                }
                showAlert('Update sent successfully.', 'success');
                form.reset();
            } catch (err) {
                showAlert(err.message || 'Unable to send update.', 'error');
            }
        });
    </script>
</body>
</html>
