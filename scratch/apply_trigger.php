<?php
require_once __DIR__ . '/../lib/oci_db.php';
$conn = db_connect();

$sql = "
CREATE OR REPLACE TRIGGER trg_slot_capacity_check
BEFORE INSERT ON \"ORDER\"
FOR EACH ROW
DECLARE
    v_current_orders NUMBER;
BEGIN
    SELECT max_orders
    INTO v_current_orders
    FROM COLLECTION_SLOT
    WHERE slot_id = :NEW.slot_id
    FOR UPDATE;
 
    IF v_current_orders >= 20 THEN
        RAISE_APPLICATION_ERROR(
            -20001,
            'Booking failed: This collection slot is fully booked (20 orders maximum). Please select a different slot.'
        );
    END IF;
    
    -- Increment the current count and check if it reaches 20 to mark as UNAVAILABLE
    UPDATE COLLECTION_SLOT
    SET max_orders = max_orders + 1,
        slot_status = CASE WHEN (max_orders + 1) >= 20 THEN 'UNAVAILABLE' ELSE slot_status END
    WHERE slot_id = :NEW.slot_id;
END;
";

$stmt = oci_parse($conn, $sql);
if (oci_execute($stmt)) {
    echo "Trigger applied successfully.\n";
} else {
    $e = oci_error($stmt);
    echo "Error: " . $e['message'] . "\n";
}
oci_free_statement($stmt);
oci_close($conn);
?>
