<?php
require_once '../includes/header.php';

$message = '';
$messageType = '';

// ?????? ID ??????
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ?????? ??????? ??????
$stmt = $db->prepare("SELECT f.*, c.name as client_name, c.phone as client_phone 
                      FROM followups f
                      JOIN clients c ON f.client_id = c.id
                      WHERE f.id = :id");
$stmt->execute(['id' => $id]);
$followup = $stmt->fetch();

if (!$followup) {
    header('Location: index.php');
    exit;
}

// ?????? ???? ???????
$stmt = $db->query("SELECT id, name, phone FROM clients ORDER BY name");
$clients = $stmt->fetchAll();

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
            $stmt = $db->prepare("UPDATE followups SET
                client_id = :client_id,
                contact_status = :contact_status,
                contact_note = :contact_note,
                next_followup_date = :next_followup_date
                WHERE id = :id");
            
            $stmt->execute([
                'client_id' => $client_id,
                'contact_status' => $contact_status,
                'contact_note' => $contact_note,
                'next_followup_date' => $next_followup_date,
                'id' => $id
            ]);
            
            $message = '?????? ?? ?????? ????????? ??.';
            $messageType = 'success';
            
            // ????????? ??????? ??????
            $followup = array_merge($followup, $_POST);
            
            // ?????? ??? ????? ????
            if ($client_id != $followup['client_id']) {
                $stmt = $db->prepare("SELECT name, phone FROM clients WHERE id = :id");
                $stmt->execute(['id' => $client_id]);
                $client_info = $stmt->fetch();
                if ($client_info) {
                    $followup['client_name'] = $client_info['name'];
                    $followup['client_phone'] = $client_info['phone'];
                }
            }
        } catch (PDOException $e) {
            $message = '??? ?? ????????? ??????: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil"></i> ?????? ??????</h1>
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
                                    <?php echo $followup['client_id'] == $client['id'] ? 'selected' : ''; ?>>
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
                                    <?php echo $followup['contact_status'] == $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">????? ?????? ????</label>
                    <input type="date" name="next_followup_date" class="form-control"
                           value="<?php echo $followup['next_followup_date']; ?>">
                    <small class="text-muted">?????? ?? ???? ?????? ???? ?????? (???????)</small>
                </div>
                
                <div class="col-md-6 mb-3" id="client_phone_section">
                    <label class="form-label">????? ???? ?????</label>
                    <div class="input-group">
                        <input type="text" id="client_phone" class="form-control" 
                               value="<?php echo htmlspecialchars($followup['client_phone']); ?>" readonly dir="ltr">
                        <?php if ($followup['client_phone']): ?>
                        <button type="button" id="call_button" class="btn btn-outline-success">
                            <i class="bi bi-telephone"></i> ????
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">??????? ????</label>
                    <textarea name="contact_note" class="form-control" rows="4"><?php echo htmlspecialchars($followup['contact_note']); ?></textarea>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>????? ???:</strong> <?php echo convertToJalali($followup['created_at']); ?>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> ?????????
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> ??????
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ???? ?????????? ??? ????? -->
<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-clock-history"></i> ???? ?????????? <?php echo htmlspecialchars($followup['client_name']); ?>
    </div>
    <div class="card-body">
        <?php
        $stmt = $db->prepare("SELECT * FROM followups 
                              WHERE client_id = :client_id AND id != :current_id 
                              ORDER BY created_at DESC 
                              LIMIT 5");
        $stmt->execute(['client_id' => $followup['client_id'], 'current_id' => $id]);
        $other_followups = $stmt->fetchAll();
        
        if (count($other_followups) > 0):
        ?>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>?????</th>
                        <th>?????</th>
                        <th>???????</th>
                        <th>?????? ????</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($other_followups as $other): ?>
                    <tr>
                        <td><?php echo convertToJalali($other['created_at']); ?></td>
                        <td>
                            <?php
                            $statusClass = match($other['contact_status']) {
                                '???? ????' => 'bg-success',
                                '??????' => 'bg-warning',
                                '???? ????' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo $other['contact_status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($other['contact_note']): ?>
                                <small><?php echo htmlspecialchars(substr($other['contact_note'], 0, 50)); ?>
                                <?php if (strlen($other['contact_note']) > 50): ?>...<?php endif; ?>
                                </small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $other['next_followup_date'] ? convertToJalali($other['next_followup_date']) : '-'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted mb-0">??? ????? ?????? ???? ??? ????? ???.</p>
        <?php endif; ?>
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
            if (callButton) callButton.remove();
            
            const newCallButton = document.createElement('button');
            newCallButton.type = 'button';
            newCallButton.className = 'btn btn-outline-success';
            newCallButton.innerHTML = '<i class="bi bi-telephone"></i> ????';
            newCallButton.onclick = function() {
                window.open('tel:' + phone);
            };
            phoneInput.parentNode.appendChild(newCallButton);
        } else {
            phoneInput.value = '';
            if (callButton) callButton.remove();
        }
    });
    
    // ????? ???? ???? ????? ????
    if (callButton) {
        callButton.onclick = function() {
            const phone = phoneInput.value;
            if (phone) {
                window.open('tel:' + phone);
            }
        };
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>