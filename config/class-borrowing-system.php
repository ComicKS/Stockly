<?php

require_once __DIR__ . '/db-config.php';

class BorrowingSystem extends Database {
    public function normalizeDateTime($value) {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
            return date('Y-m-d H:i:s', strtotime($value));
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
            return $value;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value . ' 00:00:00';
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    public function formatDateTimeForDisplay($value) {
        $normalized = $this->normalizeDateTime($value);
        if (!$normalized) {
            return '—';
        }

        $timestamp = strtotime($normalized);
        if ($timestamp === false) {
            return '—';
        }

        return date('d M Y, H:i', $timestamp);
    }

    public function getStatusTransitions($currentStatus) {
        $currentStatus = trim((string) $currentStatus);
        $allowed = [
            'Pending' => ['Approved', 'Rejected', 'Cancelled'],
            'Approved' => ['Checked Out', 'Cancelled'],
            'Rejected' => [],
            'Checked Out' => ['Returned', 'Cancelled'],
            'Returned' => [],
            'Cancelled' => [],
        ];

        return $allowed[$currentStatus] ?? [];
    }

    public function getDashboardStats() {
        $stats = [
            'pending_requests' => 0,
            'active_loans' => 0,
            'overdue_loans' => 0,
            'today_bookings' => 0,
            'recent_loans' => []
        ];

        $pendingStmt = $this->conn->prepare('SELECT COUNT(*) AS total FROM tb_loans WHERE status = ?');
        $pendingStatus = 'Pending';
        $pendingStmt->bind_param('s', $pendingStatus);
        $pendingStmt->execute();
        $pendingRow = $pendingStmt->get_result()->fetch_assoc();
        $stats['pending_requests'] = (int) ($pendingRow['total'] ?? 0);
        $pendingStmt->close();

        $activeStmt = $this->conn->prepare('SELECT COUNT(*) AS total FROM tb_loans WHERE status IN (?, ?)');
        $approvedStatus = 'Approved';
        $checkedOutStatus = 'Checked Out';
        $activeStmt->bind_param('ss', $approvedStatus, $checkedOutStatus);
        $activeStmt->execute();
        $activeRow = $activeStmt->get_result()->fetch_assoc();
        $stats['active_loans'] = (int) ($activeRow['total'] ?? 0);
        $activeStmt->close();

        $overdueStmt = $this->conn->prepare('SELECT COUNT(*) AS total FROM tb_loans WHERE status IN (?, ?) AND due_date IS NOT NULL AND due_date < NOW()');
        $overdueStmt->bind_param('ss', $approvedStatus, $checkedOutStatus);
        $overdueStmt->execute();
        $overdueRow = $overdueStmt->get_result()->fetch_assoc();
        $stats['overdue_loans'] = (int) ($overdueRow['total'] ?? 0);
        $overdueStmt->close();

        $bookingStmt = $this->conn->prepare('SELECT COUNT(*) AS total FROM tb_loans WHERE facility_id IS NOT NULL AND status IN (?, ?) AND start_date <= NOW() AND COALESCE(end_date, start_date) >= NOW()');
        $bookingStmt->bind_param('ss', $approvedStatus, $checkedOutStatus);
        $bookingStmt->execute();
        $bookingRow = $bookingStmt->get_result()->fetch_assoc();
        $stats['today_bookings'] = (int) ($bookingRow['total'] ?? 0);
        $bookingStmt->close();

        $recent = $this->conn->query('SELECT l.id, b.full_name, l.request_type, l.status, l.request_date FROM tb_loans l LEFT JOIN tb_borrowers b ON b.id = l.borrower_id ORDER BY l.request_date DESC LIMIT 5');
        if ($recent && $recent->num_rows > 0) {
            $stats['recent_loans'] = $recent->fetch_all(MYSQLI_ASSOC);
        }

        return $stats;
    }

    public function getUserDashboardStats($borrowerId) {
        $borrowerId = (int) $borrowerId;
        $stats = [
            'pending_requests' => 0,
            'approved_requests' => 0,
            'checked_out_requests' => 0,
            'returned_requests' => 0,
            'recent_loans' => []
        ];

        if ($borrowerId <= 0) {
            return $stats;
        }

        $counts = $this->conn->prepare('SELECT status, COUNT(*) AS total FROM tb_loans WHERE borrower_id = ? GROUP BY status');
        $counts->bind_param('i', $borrowerId);
        $counts->execute();
        $result = $counts->get_result();
        while ($row = $result->fetch_assoc()) {
            $status = $row['status'] ?? '';
            if ($status === 'Pending') {
                $stats['pending_requests'] = (int) $row['total'];
            } elseif ($status === 'Approved') {
                $stats['approved_requests'] = (int) $row['total'];
            } elseif ($status === 'Checked Out') {
                $stats['checked_out_requests'] = (int) $row['total'];
            } elseif ($status === 'Returned') {
                $stats['returned_requests'] = (int) $row['total'];
            }
        }
        $counts->close();

        $recent = $this->conn->prepare('SELECT l.id, l.request_type, l.status, l.start_date, l.end_date, l.purpose, l.request_date FROM tb_loans l WHERE l.borrower_id = ? ORDER BY l.request_date DESC LIMIT 5');
        $recent->bind_param('i', $borrowerId);
        $recent->execute();
        $recentResult = $recent->get_result();
        while ($row = $recentResult->fetch_assoc()) {
            $stats['recent_loans'][] = $row;
        }
        $recent->close();

        return $stats;
    }

    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare('SELECT * FROM tb_users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $user ?: null;
    }

    public function getUserById($id) {
        $stmt = $this->conn->prepare('SELECT * FROM tb_users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $user ?: null;
    }

    public function loginUser($email, $password) {
        $email = trim((string) $email);
        $password = (string) $password;
        $user = $this->getUserByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        if (!empty($user['is_active']) && (int) $user['is_active'] === 0) {
            return false;
        }

        return $user;
    }

    public function getBorrowers($keyword = '') {
        $borrowers = [];
        $query = 'SELECT * FROM tb_borrowers WHERE 1 = 1';
        $params = [];
        $types = '';

        if ($keyword !== '') {
            $query .= ' AND (full_name LIKE ? OR campus_id LIKE ? OR department LIKE ? OR email LIKE ?)';
            $search = '%' . $keyword . '%';
            $params = [$search, $search, $search, $search];
            $types = 'ssss';
        }

        $query .= ' ORDER BY full_name ASC';
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $borrowers[] = $row;
            }
            $stmt->close();
        }

        return $borrowers;
    }

    public function getBorrowerById($id) {
        $stmt = $this->conn->prepare('SELECT * FROM tb_borrowers WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $borrower = $result->fetch_assoc();
        $stmt->close();
        return $borrower ?: null;
    }

    public function saveBorrower($data) {
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $campusId = trim((string) ($data['campus_id'] ?? ''));
        $borrowerType = trim((string) ($data['borrower_type'] ?? 'Student'));
        $department = trim((string) ($data['department'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $activeStatus = trim((string) ($data['active_status'] ?? 'Active'));

        if ($fullName === '' || $campusId === '' || $department === '') {
            return false;
        }

        if (isset($data['id']) && (int) $data['id'] > 0) {
            $stmt = $this->conn->prepare('UPDATE tb_borrowers SET campus_id = ?, full_name = ?, borrower_type = ?, department = ?, phone = ?, email = ?, active_status = ? WHERE id = ?');
            $stmt->bind_param('sssssssi', $campusId, $fullName, $borrowerType, $department, $phone, $email, $activeStatus, $data['id']);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }

        $stmt = $this->conn->prepare('INSERT INTO tb_borrowers (campus_id, full_name, borrower_type, department, phone, email, active_status) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssss', $campusId, $fullName, $borrowerType, $department, $phone, $email, $activeStatus);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function deleteBorrower($id) {
        $stmt = $this->conn->prepare('DELETE FROM tb_borrowers WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getFacilities($keyword = '') {
        $facilities = [];
        $query = 'SELECT * FROM tb_facilities WHERE 1 = 1';
        $params = [];
        $types = '';

        if ($keyword !== '') {
            $query .= ' AND (name LIKE ? OR building LIKE ? OR description LIKE ?)';
            $search = '%' . $keyword . '%';
            $params = [$search, $search, $search];
            $types = 'sss';
        }

        $query .= ' ORDER BY name ASC';
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $facilities[] = $row;
            }
            $stmt->close();
        }

        return $facilities;
    }

    public function getFacilityById($id) {
        $stmt = $this->conn->prepare('SELECT * FROM tb_facilities WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $facility = $result->fetch_assoc();
        $stmt->close();
        return $facility ?: null;
    }

    public function saveFacility($data) {
        $name = trim((string) ($data['name'] ?? ''));
        $building = trim((string) ($data['building'] ?? ''));
        $capacity = (int) ($data['capacity'] ?? 0);
        $description = trim((string) ($data['description'] ?? ''));
        $availabilityStatus = trim((string) ($data['availability_status'] ?? 'Available'));
        $maintenanceStatus = trim((string) ($data['maintenance_status'] ?? 'Operational'));
        $maintenanceNotes = trim((string) ($data['maintenance_notes'] ?? ''));

        if ($name === '' || $building === '' || $capacity <= 0) {
            return false;
        }

        if (isset($data['id']) && (int) $data['id'] > 0) {
            $stmt = $this->conn->prepare('UPDATE tb_facilities SET name = ?, building = ?, capacity = ?, description = ?, availability_status = ?, maintenance_status = ?, maintenance_notes = ? WHERE id = ?');
            $stmt->bind_param('ssissssi', $name, $building, $capacity, $description, $availabilityStatus, $maintenanceStatus, $maintenanceNotes, $data['id']);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }

        $stmt = $this->conn->prepare('INSERT INTO tb_facilities (name, building, capacity, description, availability_status, maintenance_status, maintenance_notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssissss', $name, $building, $capacity, $description, $availabilityStatus, $maintenanceStatus, $maintenanceNotes);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function deleteFacility($id) {
        $stmt = $this->conn->prepare('DELETE FROM tb_facilities WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getGoods($keyword = '') {
        $goods = [];
        $query = 'SELECT * FROM tb_goods WHERE 1 = 1';
        $params = [];
        $types = '';

        if ($keyword !== '') {
            $query .= ' AND (name LIKE ? OR inventory_code LIKE ? OR category LIKE ? OR location LIKE ?)';
            $search = '%' . $keyword . '%';
            $params = [$search, $search, $search, $search];
            $types = 'ssss';
        }

        $query .= ' ORDER BY name ASC';
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $goods[] = $row;
            }
            $stmt->close();
        }

        return $goods;
    }

    public function getGoodById($id) {
        $stmt = $this->conn->prepare('SELECT * FROM tb_goods WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $good = $result->fetch_assoc();
        $stmt->close();
        return $good ?: null;
    }

    public function saveGood($data) {
        $name = trim((string) ($data['name'] ?? ''));
        $inventoryCode = trim((string) ($data['inventory_code'] ?? ''));
        $category = trim((string) ($data['category'] ?? ''));
        $location = trim((string) ($data['location'] ?? ''));
        $quantity = (int) ($data['quantity'] ?? 0);
        $conditionStatus = trim((string) ($data['condition_status'] ?? 'Good'));
        $description = trim((string) ($data['description'] ?? ''));
        $maintenanceStatus = trim((string) ($data['maintenance_status'] ?? 'Available'));
        $maintenanceNotes = trim((string) ($data['maintenance_notes'] ?? ''));

        if ($name === '' || $inventoryCode === '' || $category === '' || $location === '' || $quantity < 0) {
            return false;
        }

        if (isset($data['id']) && (int) $data['id'] > 0) {
            $stmt = $this->conn->prepare('UPDATE tb_goods SET name = ?, inventory_code = ?, category = ?, location = ?, quantity = ?, condition_status = ?, description = ?, maintenance_status = ?, maintenance_notes = ? WHERE id = ?');
            $stmt->bind_param('ssssissssi', $name, $inventoryCode, $category, $location, $quantity, $conditionStatus, $description, $maintenanceStatus, $maintenanceNotes, $data['id']);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }

        $stmt = $this->conn->prepare('INSERT INTO tb_goods (name, inventory_code, category, location, quantity, condition_status, description, maintenance_status, maintenance_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssissss', $name, $inventoryCode, $category, $location, $quantity, $conditionStatus, $description, $maintenanceStatus, $maintenanceNotes);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function deleteGood($id) {
        $stmt = $this->conn->prepare('DELETE FROM tb_goods WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getGoodsAvailability($goodId, $startDate = null, $endDate = null) {
        $good = $this->getGoodById((int) $goodId);
        if (!$good) {
            return ['total_quantity' => 0, 'reserved_quantity' => 0, 'available_quantity' => 0, 'status' => 'Unavailable'];
        }

        $totalQuantity = (int) $good['quantity'];
        $start = $this->normalizeDateTime($startDate);
        $end = $this->normalizeDateTime($endDate);
        if (!$start) {
            $start = date('Y-m-d H:i:s');
        }
        if (!$end) {
            $end = $start;
        }

        $reserved = 0;
        if ($start && $end) {
            $stmt = $this->conn->prepare('SELECT COALESCE(SUM(li.quantity), 0) AS reserved FROM tb_loans l INNER JOIN tb_loan_items li ON li.loan_id = l.id WHERE li.good_id = ? AND l.status IN (?, ?) AND l.start_date < ? AND COALESCE(l.end_date, l.start_date) > ?');
            $statusApproved = 'Approved';
            $statusChecked = 'Checked Out';
            $stmt->bind_param('issss', $goodId, $statusApproved, $statusChecked, $end, $start);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $reserved = (int) ($row['reserved'] ?? 0);
            $stmt->close();
        }

        $available = max(0, $totalQuantity - $reserved);

        if ($good['maintenance_status'] === 'Maintenance') {
            $status = 'Maintenance';
        } elseif ($available <= 0) {
            $status = 'Booked';
        } elseif ($reserved > 0) {
            $status = 'Partially booked';
        } else {
            $status = 'Available';
        }

        return [
            'total_quantity' => $totalQuantity,
            'reserved_quantity' => $reserved,
            'available_quantity' => $available,
            'status' => $status,
        ];
    }

    public function getFacilityAvailability($facilityId, $startDate = null, $endDate = null) {
        $facility = $this->getFacilityById((int) $facilityId);
        if (!$facility) {
            return ['status' => 'Unavailable', 'available' => false, 'message' => 'This facility no longer exists.'];
        }

        if ($facility['maintenance_status'] === 'Maintenance' || $facility['availability_status'] === 'Unavailable') {
            return ['status' => 'Unavailable', 'available' => false, 'message' => 'Maintenance or availability status prevents booking.'];
        }

        if (!$startDate || !$endDate) {
            return ['status' => 'Available', 'available' => true, 'message' => 'Available for the selected period.'];
        }

        $start = $this->normalizeDateTime($startDate);
        $end = $this->normalizeDateTime($endDate);
        $isBooked = $this->facilityBookingConflict((int) $facilityId, $start, $end);
        return [
            'status' => $isBooked ? 'Booked' : 'Available',
            'available' => !$isBooked,
            'message' => $isBooked ? 'This facility is already busy during the selected time range.' : 'Available for the selected period.',
        ];
    }

    public function getLoanRecords($keyword = '', $status = '') {
        $records = [];
        $query = 'SELECT l.*, b.full_name, b.campus_id, f.name AS facility_name FROM tb_loans l LEFT JOIN tb_borrowers b ON b.id = l.borrower_id LEFT JOIN tb_facilities f ON f.id = l.facility_id WHERE 1 = 1';
        $params = [];
        $types = '';

        if ($status !== '') {
            $query .= ' AND l.status = ?';
            $params[] = $status;
            $types .= 's';
        }

        if ($keyword !== '') {
            $query .= ' AND (b.full_name LIKE ? OR b.campus_id LIKE ? OR f.name LIKE ? OR l.purpose LIKE ?)';
            $search = '%' . $keyword . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= 'ssss';
        }

        $query .= ' ORDER BY l.request_date DESC, l.id DESC';
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $row['items'] = $this->getLoanItemSummary((int) $row['id']);
                $records[] = $row;
            }
            $stmt->close();
        }

        return $records;
    }

    public function getMyRequests($userId) {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return [];
        }
        $stmt = $this->conn->prepare('SELECT l.*, b.full_name, b.campus_id, f.name AS facility_name FROM tb_loans l LEFT JOIN tb_borrowers b ON b.id = l.borrower_id LEFT JOIN tb_facilities f ON f.id = l.facility_id WHERE l.borrower_id = ? ORDER BY l.request_date DESC');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $row['items'] = $this->getLoanItemSummary((int) $row['id']);
            $records[] = $row;
        }
        $stmt->close();
        return $records;
    }

    public function getLoanById($id) {
        $stmt = $this->conn->prepare('SELECT l.*, b.full_name, b.campus_id, b.department, f.name AS facility_name FROM tb_loans l LEFT JOIN tb_borrowers b ON b.id = l.borrower_id LEFT JOIN tb_facilities f ON f.id = l.facility_id WHERE l.id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $loan = $result->fetch_assoc();
        $stmt->close();

        if ($loan) {
            $loan['items'] = $this->getLoanItems($id);
        }

        return $loan ?: null;
    }

    public function getLoanItems($loanId) {
        $items = [];
        $stmt = $this->conn->prepare('SELECT li.*, g.name AS good_name, g.inventory_code, f.name AS facility_name FROM tb_loan_items li LEFT JOIN tb_goods g ON g.id = li.good_id LEFT JOIN tb_facilities f ON f.id = li.facility_id WHERE li.loan_id = ? ORDER BY li.id ASC');
        $stmt->bind_param('i', $loanId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
        return $items;
    }

    public function getLoanItemSummary($loanId) {
        $items = $this->getLoanItems($loanId);
        if (empty($items)) {
            return 'No items selected';
        }

        $summary = [];
        foreach ($items as $item) {
            if (!empty($item['facility_name'])) {
                $summary[] = $item['facility_name'];
            } elseif (!empty($item['good_name'])) {
                $summary[] = $item['good_name'] . ' x' . (int) $item['quantity'];
            }
        }

        return implode(', ', $summary);
    }

    public function saveLoan($data) {
        $borrowerId = (int) ($data['borrower_id'] ?? 0);
        $requestType = trim((string) ($data['request_type'] ?? 'Facility'));
        $facilityId = isset($data['facility_id']) && trim((string) $data['facility_id']) !== '' ? (int) $data['facility_id'] : null;
        $purpose = trim((string) ($data['purpose'] ?? ''));
        $notes = trim((string) ($data['notes'] ?? ''));
        $startDate = $this->normalizeDateTime($data['start_date'] ?? '');
        $endDate = $this->normalizeDateTime($data['end_date'] ?? '');
        $dueDate = $this->normalizeDateTime($data['due_date'] ?? '');
        $requestDate = $this->normalizeDateTime($data['request_date'] ?? date('Y-m-d H:i:s'));
        $status = 'Pending';

        if ($borrowerId <= 0 || $purpose === '' || !$startDate) {
            return false;
        }

        if ($endDate && $endDate <= $startDate) {
            return false;
        }

        if ($requestType === 'Facility' && $facilityId === null) {
            return false;
        }

        if ($requestType === 'Facility') {
            $facility = $this->getFacilityById($facilityId);
            if (!$facility || $facility['maintenance_status'] === 'Maintenance' || $facility['availability_status'] === 'Unavailable') {
                return false;
            }
            $effectiveEnd = $endDate ?: $startDate;
            if ($this->facilityBookingConflict($facilityId, $startDate, $effectiveEnd)) {
                return false;
            }
        }

        if ($requestType === 'Goods') {
            $goods = $data['goods'] ?? [];
            if (empty($goods)) {
                return false;
            }

            foreach ($goods as $goodItem) {
                $goodId = (int) ($goodItem['good_id'] ?? 0);
                $quantity = (int) ($goodItem['quantity'] ?? 0);
                if ($goodId <= 0 || $quantity <= 0) {
                    return false;
                }

                $good = $this->getGoodById($goodId);
                if (!$good || $good['maintenance_status'] === 'Maintenance') {
                    return false;
                }

                $effectiveEnd = $endDate ?: $startDate;
                $availability = $this->getGoodsAvailability($goodId, $startDate, $effectiveEnd);
                if ($quantity > $availability['available_quantity']) {
                    return false;
                }
            }
        }

        $this->conn->begin_transaction();
        try {
            if ($requestType === 'Goods' && !$dueDate) {
                $dueDate = $endDate ?: $startDate;
            }

            $stmt = $this->conn->prepare('INSERT INTO tb_loans (borrower_id, request_type, facility_id, purpose, notes, request_date, start_date, end_date, due_date, issue_date, return_date, status, condition_on_return) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, ?, NULL)');
            $stmt->bind_param('isisssssss', $borrowerId, $requestType, $facilityId, $purpose, $notes, $requestDate, $startDate, $endDate, $dueDate, $status);
            $stmt->execute();
            $loanId = $this->conn->insert_id;
            $stmt->close();

            if ($requestType === 'Facility') {
                $itemStmt = $this->conn->prepare('INSERT INTO tb_loan_items (loan_id, facility_id, quantity) VALUES (?, ?, ?)');
                $quantity = 1;
                $itemStmt->bind_param('iii', $loanId, $facilityId, $quantity);
                $itemStmt->execute();
                $itemStmt->close();
            } else {
                $itemStmt = $this->conn->prepare('INSERT INTO tb_loan_items (loan_id, good_id, quantity) VALUES (?, ?, ?)');
                foreach ($data['goods'] as $goodItem) {
                    $goodId = (int) ($goodItem['good_id'] ?? 0);
                    $quantity = (int) ($goodItem['quantity'] ?? 0);
                    $itemStmt->bind_param('iii', $loanId, $goodId, $quantity);
                    $itemStmt->execute();
                }
                $itemStmt->close();
            }

            $this->conn->commit();
            return $loanId;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function updateLoanStatus($loanId, $status, $extra = []) {
        $loan = $this->getLoanById((int) $loanId);
        if (!$loan) {
            return false;
        }

        $currentStatus = $loan['status'];
        $allowed = $this->getStatusTransitions($currentStatus);
        $status = trim((string) $status);

        if (!in_array($status, $allowed, true)) {
            return false;
        }

        if ($status === 'Approved' || $status === 'Checked Out') {
            if ($loan['request_type'] === 'Facility') {
                $facilityId = (int) $loan['facility_id'];
                $rangeStart = $this->normalizeDateTime($loan['start_date']) ?: date('Y-m-d H:i:s');
                $rangeEnd = $this->normalizeDateTime($loan['end_date']) ?: $rangeStart;
                if ($this->facilityBookingConflict($facilityId, $rangeStart, $rangeEnd)) {
                    return false;
                }
            } else {
                foreach ($loan['items'] as $item) {
                    if (!empty($item['good_id'])) {
                        $goodId = (int) $item['good_id'];
                        $quantity = (int) $item['quantity'];
                        $rangeStart = $this->normalizeDateTime($loan['start_date']) ?: date('Y-m-d H:i:s');
                        $rangeEnd = $this->normalizeDateTime($loan['end_date']) ?: $rangeStart;
                        $available = $this->getGoodsAvailability($goodId, $rangeStart, $rangeEnd);
                        if ($quantity > $available['available_quantity']) {
                            return false;
                        }
                    }
                }
            }
        }

        $this->conn->begin_transaction();
        try {
            $issueDate = $this->normalizeDateTime($extra['issue_date'] ?? $loan['issue_date']);
            $returnDate = $this->normalizeDateTime($extra['return_date'] ?? $loan['return_date']);
            $dueDate = $this->normalizeDateTime($extra['due_date'] ?? $loan['due_date']);
            $conditionOnReturn = $extra['condition_on_return'] ?? $loan['condition_on_return'];

            if ($status === 'Checked Out' && empty($issueDate)) {
                $issueDate = date('Y-m-d H:i:s');
            }

            if ($status === 'Returned' && empty($returnDate)) {
                $returnDate = date('Y-m-d H:i:s');
            }

            $stmt = $this->conn->prepare('UPDATE tb_loans SET status = ?, issue_date = ?, return_date = ?, due_date = ?, condition_on_return = ? WHERE id = ?');
            $stmt->bind_param('sssssi', $status, $issueDate, $returnDate, $dueDate, $conditionOnReturn, $loanId);
            $stmt->execute();
            $stmt->close();
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function facilityBookingConflict($facilityId, $startDate, $endDate) {
        if ($facilityId <= 0 || $startDate === '') {
            return false;
        }

        $overlapStart = $this->normalizeDateTime($startDate);
        $overlapEnd = $this->normalizeDateTime($endDate) ?: $overlapStart;
        $query = 'SELECT COUNT(*) AS total FROM tb_loans WHERE facility_id = ? AND status IN (?, ?) AND start_date < ? AND COALESCE(end_date, start_date) > ?';
        $stmt = $this->conn->prepare($query);
        $approved = 'Approved';
        $checkedOut = 'Checked Out';
        $stmt->bind_param('issss', $facilityId, $approved, $checkedOut, $overlapEnd, $overlapStart);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return ((int) ($row['total'] ?? 0)) > 0;
    }

    public function deleteLoan($id) {
        $stmt = $this->conn->prepare('DELETE FROM tb_loans WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getStatusOptions() {
        return ['Pending', 'Approved', 'Rejected', 'Checked Out', 'Returned', 'Cancelled'];
    }
}
