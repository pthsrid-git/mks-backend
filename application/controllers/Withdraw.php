<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Withdraw extends CI_Controller // kalau punya MY_Controller admin, ganti ke itu
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'form']);
        $this->load->library('session');

        // TODO: cek login admin di sini kalau perlu
        // if (!$this->session->userdata('is_admin')) redirect('login');
    }

    public function index()
    {
        $this->db->select('wr.*, u.username, u.email');
        $this->db->from('withdraw_requests wr');
        $this->db->join('users u', 'u.id = wr.user_id', 'left');
        $this->db->order_by('wr.created_at', 'DESC');
        $data['rows'] = $this->db->get()->result_array();

        $data['title'] = 'Permintaan Tarik Saldo';

        // sesuaikan dengan layout admin kamu
        $this->load->view('templates/admin_header', $data);
        $this->load->view('admin/withdraw', $data);
        $this->load->view('templates/admin_footer');
    }

    public function approve($id)
    {
        $id = (int)$id;
        if ($id <= 0) show_404();

        $this->db->trans_begin();

        // lock row withdraw
        $wr = $this->db->query(
            "SELECT * FROM withdraw_requests WHERE id=? FOR UPDATE",
            [$id]
        )->row();

        if (!$wr) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan');
            redirect('admin/withdraw');
        }

        if ($wr->status !== 'pending') {
            $this->session->set_flashdata('error', 'Status sudah '.$wr->status);
            redirect('admin/withdraw');
        }

        // ambil user + lock
        $user = $this->db->query(
            "SELECT * FROM users WHERE id=? FOR UPDATE",
            [$wr->user_id]
        )->row();

        if (!$user) {
            $this->session->set_flashdata('error', 'User tidak ditemukan');
            redirect('admin/withdraw');
        }

        $balance = (float)$user->balance;
        $amount  = (float)$wr->amount;

        if ($balance < $amount) {
            $this->session->set_flashdata(
                'error',
                'Saldo user tidak mencukupi untuk approve (saldo: '.$balance.')'
            );
            redirect('admin/withdraw');
        }

        // potong saldo user
        $this->db->where('id', $user->id);
        $this->db->update('users', [
            'balance' => $balance - $amount,
        ]);

        // update status withdraw
        $this->db->where('id', $wr->id);
        $this->db->update('withdraw_requests', [
            'status'     => 'approved',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Gagal approve penarikan');
        } else {
            $this->db->trans_commit();
            $this->session->set_flashdata('success', 'Penarikan berhasil di-approve');
        }

        redirect('admin/withdraw');
    }

    public function reject($id)
    {
        $id = (int)$id;
        if ($id <= 0) show_404();

        $wr = $this->db->get_where('withdraw_requests', ['id' => $id])->row();
        if (!$wr) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan');
            redirect('admin/withdraw');
        }

        if ($wr->status !== 'pending') {
            $this->session->set_flashdata('error', 'Status sudah '.$wr->status);
            redirect('admin/withdraw');
        }

        $this->db->where('id', $id);
        $this->db->update('withdraw_requests', [
            'status'     => 'rejected',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->session->set_flashdata('success', 'Penarikan ditolak');
        redirect('admin/withdraw');
    }
}
