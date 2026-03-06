<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promo_model extends CI_Model {

    public function get_active_promos($limit = 10)
    {
        $now = date('Y-m-d H:i:s');

        $this->db->from('mks_promos');
        $this->db->where('is_active', 1);
        // kalau pakai periode aktif
        $this->db->group_start()
            ->where('start_at IS NULL', null, false)
            ->or_where('start_at <=', $now);
        $this->db->group_end();
        $this->db->group_start()
            ->where('end_at IS NULL', null, false)
            ->or_where('end_at >=', $now);
        $this->db->group_end();

        $this->db->order_by('sort_order', 'DESC');
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);

        $query = $this->db->get();
        $rows  = $query->result_array();

        // mapping ke format yang dipakai Flutter
        $base = rtrim(base_url(), '/');
        foreach ($rows as &$row) {
            $row['image_url'] = null;
            if (!empty($row['image'])) {
                // hasilnya: https://domain.com/uploads/promos/nama_file.png
                $row['image_url'] = $base . '/uploads/promo/' . $row['image'];
            }
            // kalau mau, title/subtitle sudah aman jadi string
            $row['title']    = (string) $row['title'];
            $row['subtitle'] = (string) $row['subtitle'];
        }

        return $rows;
    }
}
