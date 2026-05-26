<?php
/**
 * Test script to check reservation creation
 */
require_once '../config/database.php';
require_once '../classes/Reserva.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "Database connection successful\n";

    // Create a test reservation
    $reserva = new Reserva($db);
    $reserva->nome = 'Test User';
    $reserva->email = 'test@example.com';
    $reserva->telefone = '+351912345678';
    $reserva->data_reserva = date('Y-m-d', strtotime('+1 day'));
    $reserva->hora_reserva = '19:00:00';
    $reserva->num_pessoas = 2;
    $reserva->observacoes = 'Test reservation';

    echo "Validating reservation...\n";
    $erros = $reserva->validar();
    if (!empty($erros)) {
        echo "Validation errors: " . implode(', ', $erros) . "\n";
        exit;
    }

    echo "Checking availability...\n";
    $disponivel = $reserva->verificarDisponibilidade($reserva->data_reserva, $reserva->hora_reserva, $reserva->num_pessoas);
    if (!$disponivel) {
        echo "No availability for this time slot\n";
        exit;
    }

    echo "Creating reservation...\n";
    if ($reserva->criar()) {
        echo "Reservation created successfully with ID: " . $reserva->id . "\n";
    } else {
        echo "Failed to create reservation\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>