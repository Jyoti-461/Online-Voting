<?php
session_start();
require_once 'includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/lib/fpdf.php';

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    die('Access denied - please login');
}

class PDF extends FPDF {
    function Header() {
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 10, 'ONLINE VOTING SYSTEM REPORT', 0, 1, 'C');
        $this->SetFont('helvetica', '', 12);
        $this->Cell(0, 10, 'Generated: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
        $this->Ln(10);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
    
    function BasicTable($header, $data, $colWidths) {
        // Header
        $this->SetFont('helvetica', 'B', 12);
        for($i = 0; $i < count($header); $i++) {
            $this->Cell($colWidths[$i], 7, $header[$i], 1);
        }
        $this->Ln();
        // Data
        $this->SetFont('helvetica', '', 12);
        foreach ($data as $row) {
            for($i = 0; $i < count($row); $i++) {
                $this->Cell($colWidths[$i], 6, $row[$i], 1);
            }
            $this->Ln();
        }
    }
}

try {
    // Initialize default values
    $currentData = [
        'candidates' => [],
        'totals' => [
            'votes' => 0,
            'candidates' => 0,
            'voters' => 0,
            'voted' => 0
        ]
    ];

    // Get current election data with error handling
    $candidates = $pdo->query("SELECT * FROM candidates ORDER BY votes DESC");
    if ($candidates) {
        $currentData['candidates'] = $candidates->fetchAll(PDO::FETCH_ASSOC);
    }

    $currentData['totals']['votes'] = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn() ?: 0;
    $currentData['totals']['candidates'] = $pdo->query("SELECT COUNT(*) FROM candidates")->fetchColumn() ?: 0;
    $currentData['totals']['voters'] = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin_dba = 0")->fetchColumn() ?: 0;
    $currentData['totals']['voted'] = $pdo->query("SELECT COUNT(*) FROM users WHERE has_voted = 1 AND is_admin_dba = 0")->fetchColumn() ?: 0;

    // Get last election data with null checks
    $lastElection = null;
    $lastData = null;
    $lastElectionResult = $pdo->query("SELECT * FROM elections ORDER BY election_date DESC LIMIT 1");
    if ($lastElectionResult) {
        $lastElection = $lastElectionResult->fetch(PDO::FETCH_ASSOC);
        if ($lastElection && !empty($lastElection['snapshot_data'])) {
            $lastData = json_decode($lastElection['snapshot_data'], true);
            // Ensure lastData has expected structure
            if (!isset($lastData['totals'])) {
                $lastData['totals'] = [
                    'votes' => 0,
                    'candidates' => 0,
                    'voters' => 0,
                    'voted' => 0
                ];
            }
        }
    }

    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();

    // 1. Current Election Summary - with division by zero protection
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'CURRENT ELECTION STATISTICS', 0, 1);
    $pdf->SetFont('helvetica', '', 12);

    $voterTurnout = ($currentData['totals']['voters'] > 0) 
        ? round(($currentData['totals']['voted'] / $currentData['totals']['voters']) * 100, 2)
        : 0;

    $summaryData = [
        ['Total Candidates', $currentData['totals']['candidates']],
        ['Registered Voters', $currentData['totals']['voters']],
        ['Votes Cast', $currentData['totals']['votes']],
        ['Voter Turnout', $voterTurnout . '%']
    ];

    $pdf->BasicTable(['Metric', 'Value'], $summaryData, [70, 70]);
    $pdf->Ln(10);

    // 2. Comparison with Last Election (if available)
    if ($lastElection && $lastData) {
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'COMPARISON WITH LAST ELECTION', 0, 1);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Last election: ' . date('Y-m-d', strtotime($lastElection['election_date'])), 0, 1);
        $pdf->Ln(5);

        // Calculate turnout percentages safely
        $currentTurnout = ($currentData['totals']['voters'] > 0) 
            ? round(($currentData['totals']['voted'] / $currentData['totals']['voters']) * 100, 2)
            : 0;
            
        $lastTurnout = ($lastData['totals']['voters'] > 0) 
            ? round(($lastData['totals']['voted'] / $lastData['totals']['voters']) * 100, 2)
            : 0;
            
        $turnoutDifference = $currentTurnout - $lastTurnout;

        // Overall comparison
        $comparisonData = [
            ['Metric', 'Current', 'Last Election', 'Difference'],
            ['Candidates', $currentData['totals']['candidates'], $lastElection['total_candidates'] ?? 0, 
             $currentData['totals']['candidates'] - ($lastElection['total_candidates'] ?? 0)],
            ['Voters', $currentData['totals']['voters'], $lastElection['total_voters'] ?? 0, 
             $currentData['totals']['voters'] - ($lastElection['total_voters'] ?? 0)],
            ['Votes', $currentData['totals']['votes'], $lastElection['total_votes'] ?? 0, 
             $currentData['totals']['votes'] - ($lastElection['total_votes'] ?? 0)],
            ['Turnout', $currentTurnout.'%', $lastTurnout.'%',
             ($turnoutDifference > 0 ? '+' : '').$turnoutDifference.'%']
        ];

        $pdf->BasicTable(['Metric', 'Current', 'Last', '± Difference'], $comparisonData, [50, 40, 40, 40]);
        $pdf->Ln(10);

        // Candidate comparison only if we have candidate data
        if (!empty($lastData['candidates'])) {
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'CANDIDATE PERFORMANCE COMPARISON', 0, 1);
            $pdf->SetFont('helvetica', '', 12);

            $candidateComparison = [];
            foreach ($currentData['candidates'] as $currentCandidate) {
                $lastVotes = 0;
                // Find matching candidate in last election
                foreach ($lastData['candidates'] as $lastCandidate) {
                    if (isset($lastCandidate['id']) && $lastCandidate['id'] == $currentCandidate['id']) {
                        $lastVotes = $lastCandidate['votes'] ?? 0;
                        break;
                    }
                }
                
                $voteChange = $currentCandidate['votes'] - $lastVotes;
                $candidateComparison[] = [
                    $currentCandidate['name'] ?? 'Unknown',
                    $currentCandidate['party'] ?? 'Unknown',
                    $currentCandidate['votes'],
                    $lastVotes,
                    $voteChange,
                    ($voteChange > 0 ? '+' : '') . $voteChange
                ];
            }

            $pdf->BasicTable(
                ['Candidate', 'Party', 'Current Votes', 'Last Votes', 'Change', '+/-'], 
                $candidateComparison,
                [50, 40, 30, 30, 30, 20]
            );
        }
    }

    // 3. Current Candidate Performance - with division by zero protection
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'CURRENT CANDIDATE PERFORMANCE', 0, 1);
    $pdf->SetFont('helvetica', '', 12);

    $candidateData = [];
    foreach ($currentData['candidates'] as $candidate) {
        $votePercent = ($currentData['totals']['votes'] > 0)
            ? round(($candidate['votes'] / $currentData['totals']['votes']) * 100, 2)
            : 0;
            
        $candidateData[] = [
            $candidate['name'] ?? 'Unknown',
            $candidate['party'] ?? 'Unknown',
            $candidate['votes'],
            $votePercent . '%'
        ];
    }

    $pdf->BasicTable(['Candidate', 'Party', 'Votes', 'Percentage'], $candidateData, [60, 40, 40, 40]);
    
    // Output PDF
    $pdf->Output('D', 'election_report_' . date('Y-m-d') . '.pdf');
    exit;

} catch (Exception $e) {
    die('Error generating PDF: ' . $e->getMessage());
}