<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Services\AdminLogService;
use App\Services\AdminPlayerService;
use App\Services\AuthService;
use App\Services\PermissionService;
use App\Services\TicketService;

final class TicketController
{
    public function create(): void
    {
        $user = AuthService::requireLogin();
        Security::requireCsrf('login');
        $file = !empty($_FILES['attachment']['tmp_name']) ? $_FILES['attachment'] : null;
        $result = TicketService::createTicket(
            (int) $user['account_id'],
            (string) $user['login'],
            (int) ($_POST['category_id'] ?? 0),
            (string) ($_POST['subject'] ?? ''),
            (string) ($_POST['body'] ?? ''),
            is_array($file) ? $file : null
        );
        Session::flash('panel_section', 'destek');
        if (!empty($result['ok'])) {
            Session::flash('panel_success', 'Ticket oluşturuldu: ' . ($result['code'] ?? ''));
        } else {
            Session::flash('panel_errors', $result['errors'] !== [] ? $result['errors'] : ['Ticket oluşturulamadı.']);
        }
        redirect('/panel');
    }

    public function replyUser(): void
    {
        $user = AuthService::requireLogin();
        Security::requireCsrf('login');
        $ticketId = (int) ($_POST['ticket_id'] ?? 0);
        $file = !empty($_FILES['attachment']['tmp_name']) ? $_FILES['attachment'] : null;
        $result = TicketService::reply(
            $ticketId,
            (int) $user['account_id'],
            (string) $user['login'],
            (string) ($_POST['body'] ?? ''),
            false,
            is_array($file) ? $file : null
        );
        Session::flash('panel_section', 'destek');
        if (!empty($result['ok'])) {
            Session::flash('panel_success', 'Yanıtın gönderildi.');
        } else {
            Session::flash('panel_errors', $result['errors'] !== [] ? $result['errors'] : ['Yanıt gönderilemedi.']);
        }
        redirect('/panel?ticket=' . $ticketId);
    }

    public function viewUser(): void
    {
        $user = AuthService::requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $ticket = TicketService::getTicket($id, (int) $user['account_id']);
        header('Content-Type: application/json; charset=utf-8');
        if ($ticket === null) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Ticket bulunamadı.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode([
            'ok' => true,
            'ticket' => $ticket,
            'allowed_types' => TicketService::allowedFileTypes(true),
            'can_reply' => ($ticket['status_code'] ?? '') === TicketService::STATUS_WAIT_PLAYER,
            'closed' => ($ticket['status_code'] ?? '') === TicketService::STATUS_CLOSED,
        ], JSON_UNESCAPED_UNICODE);
    }

    public function replyAdmin(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_TICKETS);
        Security::requireCsrf('login');
        $ticketId = (int) ($_POST['ticket_id'] ?? 0);
        $file = !empty($_FILES['attachment']['tmp_name']) ? $_FILES['attachment'] : null;
        $result = TicketService::reply(
            $ticketId,
            (int) $user['account_id'],
            (string) $user['login'],
            (string) ($_POST['body'] ?? ''),
            true,
            is_array($file) ? $file : null
        );
        Session::flash('panel_section', 'destekler');
        if (!empty($result['ok'])) {
            Session::flash('panel_success', 'Yanıt gönderildi.');
            $ticket = TicketService::getTicket($ticketId);
            AdminLogService::write(
                $user,
                'Ticket yanıtı',
                (string) ($ticket['public_code'] ?? ('#' . $ticketId)),
                $ticket ? (int) $ticket['account_id'] : null,
                $ticket ? (string) $ticket['account_login'] : null
            );
        } else {
            Session::flash('panel_errors', $result['errors'] !== [] ? $result['errors'] : ['Yanıt gönderilemedi.']);
        }
        redirect('/admin?section=destekler&ticket=' . $ticketId);
    }

    public function closeAdmin(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_TICKETS);
        Security::requireCsrf('login');
        $ticketId = (int) ($_POST['ticket_id'] ?? 0);
        $ticket = TicketService::getTicket($ticketId);
        $result = TicketService::closeTicket($ticketId);
        Session::flash('panel_section', 'destekler');
        if (!empty($result['ok'])) {
            Session::flash('panel_success', 'Ticket kapatıldı.');
            AdminLogService::write(
                $user,
                'Ticket kapatıldı',
                (string) ($ticket['public_code'] ?? ('#' . $ticketId)),
                $ticket ? (int) $ticket['account_id'] : null,
                $ticket ? (string) $ticket['account_login'] : null
            );
        } else {
            Session::flash('panel_errors', $result['errors'] !== [] ? $result['errors'] : ['Kapatılamadı.']);
        }
        redirect('/admin?section=destekler&ticket=' . $ticketId);
    }

    public function adminDetailJson(): void
    {
        PermissionService::requireFlag(PermissionService::FLAG_TICKETS);
        $id = (int) ($_GET['id'] ?? 0);
        $ticket = TicketService::getTicket($id);
        header('Content-Type: application/json; charset=utf-8');
        if ($ticket === null) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Ticket yok'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $account = null;
        if (PermissionService::userHasFlag(AuthService::user(), PermissionService::FLAG_PLAYER_DETAIL)) {
            $detail = AdminPlayerService::accountDetail((int) $ticket['account_id']);
            $account = $detail['account'] ?? null;
        }
        echo json_encode(['ok' => true, 'ticket' => $ticket, 'account' => $account], JSON_UNESCAPED_UNICODE);
    }
}
