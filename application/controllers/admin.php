<?php
defined('BASEPATH') or exit('No direct script access allowed');

class admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_admin');
        $this->load->library('datatables');
        $this->load->library('upload');
        $this->load->library('pdf');
        $this->load->library('form_validation');
        if (!$this->session->userdata('email')) {
            redirect('auth');
        }
    }

    public function index()
    {
        // // CHART JS
        // $query =  $this->db->query("SELECT COUNT(id_pengaduan) as count,MONTHNAME(tgl_pengaduan) as month_name FROM pengaduan WHERE YEAR(tgl_pengaduan) = '" . date('Y') . "'
        // GROUP BY YEAR(tgl_pengaduan),MONTH(tgl_pengaduan)");
        // $record = $query->result();
        // $data = [];
        // foreach ($record as $row) {
        //     $data['label'][] = $row->month_name;
        //     $data['data'][] = (int) $row->count;
        // }
        // $data['chart_data'] = json_encode($data);
        // // CHART JS
        // // hiasan
        // $data['total_user'] = $this->M_admin->hitungJumlahrakyat();
        // $data['total_proses'] = $this->M_admin->jumlah_proses();
        // $data['total_selesai'] = $this->M_admin->jumlah_selesai();
        // $data['total_kasus'] = $this->M_admin->hitungJumlahkasus();

        $data['title'] = 'Dashboard';
        $data['navbar'] = 'Dashboard';
        $data['user'] = $this->db->get_where('login', ['email' => $this->session->userdata('email')])->row_array();
        $this->load->view('admin/dashboard', $data);
    }

    public function table()
    {
        $data['total_user'] = $this->M_admin->hitungJumlahrakyat();
        $data['title'] = 'Table User';
        $data['navbar'] = 'Table User';
        $data['user'] = $this->db->get_where('login', ['email' => $this->session->userdata('email')])->row_array();
        $this->load->view('admin/table_rud', $data);
    }

    public function profile()
    {
        $data['title'] = 'Profile';
        $data['navbar'] = 'Profile';
        $data['user'] = $this->db->get_where('login', ['email' => $this->session->userdata('email')])->row_array();
        $this->load->view('admin/user_profile', $data);
    }
    public function editprofile()
    {
        $data['title'] = 'Profile';
        $data['navbar'] = 'Profile';
        $data['user'] = $this->db->get_where('login', ['email' => $this->session->userdata('email')])->row_array();

        $this->form_validation->set_rules('name', 'Name', 'required|trim', [
            'required' => 'Please enter a Name in the field!',
        ]);
        $this->form_validation->set_rules('telp', 'Telp', 'required|trim', [
            'required' => 'Please enter a Mobile Phone number in the field!',
        ]);



        if ($this->form_validation->run() == false) {
            $this->load->view('admin/user_profile', $data);
        } else {
            //

            $name = $this->input->post('name');
            $email = $this->input->post('email');
            $telp = $this->input->post('telp');

            $upload_image = $_FILES['foto']['name'];

            if ($upload_image) {

                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_size'] = '2048';
                $config['upload_path'] = './assets/img/profile/';

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('foto')) {
                    $old_image = $data['user']['foto'];
                    if ($old_image != 'default.jpg') {
                        unlink('assets/img/profile/' . $old_image);
                    }
                    $new_image = $this->upload->data('file_name');
                    $this->db->set('foto', $new_image);
                } else {
                    echo $this->upload->display_errors();
                }
            }

            $this->db->set('nama', $name);
            $this->db->set('telp', $telp);
            $this->db->where('email', $email);
            $this->db->update('login');
            $this->session->set_flashdata('message', '<div class="alert alert-success mt-3" role="alert">
            congratulations, the account has been updated! </div>');
            redirect('admin/profile');
        }
    }

    function get_rakyat_json()
    {     //get product data and encode to be JSON object
        echo $this->M_admin->get_all_rakyat();
    }

    function insert()
    { //update record method
        $this->M_admin->insertdata();
        redirect('admin/table');
    }

    function update()
    { //update record method
        $this->M_admin->update_rakyat();
        redirect('admin/table');
    }

    function delete()
    { //delete record method
        $this->M_admin->delete_rakyat();
        redirect('admin/table');
    }

    public function pdfdetails() //CETAK PDF
    {
        $html_content = '<h3 align="center">Laporan PDF</h3>';
        $html_content .= $this->M_admin->fetch_single_details();
        $this->pdf->loadHtml($html_content);
        $this->pdf->render();
        $this->pdf->stream("asd.pdf", array("Attachment" => 0));
    }

    public function exl()
    { //CETAL EXCEL
        $data = array(
            'title' => 'Laporan Excel',
            'user' => $this->M_admin->get_user()
        );
        $this->load->view('print', $data);
    }

    public function insertray()
    {
        $nik   = $this->input->post('nik');
        $name   = $this->input->post('nama');
        $telp   = $this->input->post('telp');
        $email   = $this->input->post('email');


        // get foto
        $config['upload_path'] = './assets/img/';
        $config['allowed_types'] = 'jpg|png|jpeg|gif';
        $config['max_size'] = '2048';  //2MB max
        $config['max_width'] = '4480'; // pixel
        $config['max_height'] = '4480'; // pixel
        $config['file_name'] = $_FILES['fotoa']['name'];

        $this->upload->initialize($config);


        if (!empty($_FILES['fotoa']['name'])) {
            if ($this->upload->do_upload('fotoa')) {
                $foto = $this->upload->data();
                $data = array(
                    'nik'       => $nik,
                    'nama'       => $name,

                    'telp'       => $telp,
                    'email'       => $email,
                    'foto'       => $foto['file_name'],
                );
                $this->db->insert('masyarakat', $data);
                redirect('asd');
            } else {
                die("gagal upload");
            }
        } else {
            echo "tidak masuk";
        }
    }
}
