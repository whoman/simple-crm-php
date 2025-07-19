<?php
require_once '../includes/header.php';

$message = '';
$messageType = '';

// ?????? ???? ???????
$stmt = $db->query("SELECT id, name, phone FROM clients ORDER BY name");
$clients = $stmt->fetchAll();

// ????? ??? client_id ?? URL ???? ????
$selected_client = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = intval($_POST['client_id']);
    $contact_status = cleanInput($_POST['contact_status']);
    $contact_note = cleanInput($_POST['contact_note']);
    $next_followup_date = !empty($_POST['next_followup_date']) ? $_POST['next_followup_date'] : null;
    
    if (!$client_id) {
        $message = '?????? ????? ?????? ???.';
        $messageType = 'danger';
    } elseif (empty($contact_status)) {
        $message = '????? ???? ?????? ???.';
        $messageType = 'danger';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO followups 
                (client_id, contact_status, contact_note, next_followup_date) 
                VALUES (:client_id, :contact_status, :contact_note, :next_followup_date)");
            
            $stmt->execute([
                'client_id' => $client_id,
                'contact_status' => $contact_status,
                'contact_note' => $contact_note,
                'next_followup_date' => $next_followup_date
            ]);
            
            header('Location: index.php?message=' . urlencode('?????? ???? ?? ?????? ??? ??.') . '&type=success');
            exit;
        } catch (PDOException $e) {
            $message = '??? ?? ??? ??????: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-plus-square"></i> ?????? ????</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-right"></i> ??????
    </a>
</div>

<?php if ($message): ?>
    <?php echo showMessage($messageType, $message); ?>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">????? <span class="text-danger">*</span></label>
                    <select name="client_id" id="client_id" class="form-select" required>
                        <option value="">-- ?????? ????? --</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?php echo $client['id']; ?>" 
                                    data-phone="<?php echo htmlspecialchars($client['phone']); ?>"
                                    <?php echo ($selected_client == $client['id'] || (isset($_POST['client_id']) && $_POST['client_id'] == $client['id'])) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($client['name']); ?>
                                <?php if ($client['phone']): ?>
                                    (<?php echo htmlspecialchars($client['phone']); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">????? ???? <span class="text-danger">*</span></label>
                    <select name="contact_status" class="form-select" required>
                        <option value="">-- ?????? ????? --</option>
                        <?php foreach (getContactStatuses() as $key => $value): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo (isset($_POST['contact_status']) && $_POST['contact_status'] == $key) ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">????? ?????? ????</label>
                    <input type="date" name="next_followup_date" class="form-control"
                           value="<?php echo isset($_POST['next_followup_date']) ? $_POST['next_followup_date'] : ''; ?>">
                    <small class="text-muted">?????? ?? ???? ?????? ???? ?????? (???????)</small>
                </div>
                
                <div class="col-md-6 mb-3" id="client_phone_section" style="display: none;">
                    <label class="form-label">????? ???? ?????</label>
                    <div class="input-group">
                        <input type="text" id="client_phone" class="form-control" readonly dir="ltr">
                        <button type="button" id="call_button" class="btn btn-outline-success" style="display: none;">
                            <i class="bi bi-telephone"></i> ????
                        </button>
                    </div>
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">??????? ????</label>
                    <textarea name="contact_note" class="form-control" rows="4"
                              placeholder="????? ????? ??????????? ??????????..."><?php echo isset($_POST['contact_note']) ? $_POST['contact_note'] : ''; ?></textarea>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> ????? ??????
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> ??????
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ?????? -->
<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-info-circle"></i> ??????
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <h6><span class="badge bg-success">???? ????</span></h6>
                <p class="small">????? ???? ??? ?? ???? ??? ? ???? ?????.</p>
            </div>
            <div class="col-md-4">
                <h6><span class="badge bg-warning">??????</span></h6>
                <p class="small">????? ??? ?????? ???? ???? ?? ????? ????? ???.</p>
            </div>
            <div class="col-md-4">
                <h6><span class="badge bg-danger">???? ????</span></h6>
                <p class="small">????? ???? ??? ?? ???? ???? ?? ?? ????? ????.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clientSelect = document.getElementById('client_id');
    const phoneSection = document.getElementById('client_phone_section');
    const phoneInput = document.getElementById('client_phone');
    const callButton = document.getElementById('call_button');
    
    clientSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const phone = selectedOption.getAttribute('data-phone');
        
        if (phone && phone.trim() !== '') {
            phoneInput.value = phone;
            phoneSection.style.display = 'block';
            callButton.style.display = 'block';
            callButton.onclick = function() {
                window.open('tel:' + phone);
            };
        } else {
            phoneSection.style.display = 'none';
            callButton.style.display = 'none';
        }
    });
    
    // ??? ????? ?? ??? ?????? ???? ????? ?? ????? ???
    if (clientSelect.value) {
        clientSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>