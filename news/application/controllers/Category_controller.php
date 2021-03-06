<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Category_controller extends Admin_Core_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Categories
     */
    public function categories()
    {
        check_permission('categories');
        $data['title'] = trans("categories");
        $data['categories'] = $this->category_model->get_parent_categories();
        $data['admin_settings'] = $this->post_admin_model;
        $data['lang_search_column'] = 2;

        $this->load->view('admin/includes/_header', $data);
        $this->load->view('admin/category/categories', $data);
        $this->load->view('admin/includes/_footer');
    }

    /**
     * Add Category Post
     */
    public function add_category_post()
    {
        check_permission('categories');
        //validate inputs
        $this->form_validation->set_rules('name', trans("category_name"), 'required|xss_clean|max_length[200]');
        $this->form_validation->set_rules('color', trans("category_color"), 'required|xss_clean|max_length[200]');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('errors_form', validation_errors());
            $this->session->set_flashdata('form_data', $this->category_model->input_values());
            redirect($this->agent->referrer());
        } else {
            if ($this->category_model->add_category()) {
                //last id
                $last_id = $this->db->insert_id();
                //update slug
                $this->category_model->update_slug($last_id);

                $this->session->set_flashdata('success_form', trans("category") . " " . trans("msg_suc_added"));
                reset_cache_data_on_change();
                redirect($this->agent->referrer());
            } else {
                $this->session->set_flashdata('form_data', $this->category_model->input_values());
                $this->session->set_flashdata('error_form', trans("msg_error"));
                redirect($this->agent->referrer());
            }
        }
    }

    /**
     * Update Category
     */
    public function update_category($id)
    {
        check_permission('categories');
        $data['title'] = trans("update_category");
        //get category
        $data['category'] = $this->category_model->get_category($id);
        $data['admin_settings'] = $this->post_admin_model;
        if (empty($data['category'])) {
            redirect($this->agent->referrer());
        }

        $this->load->view('admin/includes/_header', $data);
        $this->load->view('admin/category/update_category', $data);
        $this->load->view('admin/includes/_footer');
    }

    /**
     * Update Category Post
     */
    public function update_category_post()
    {
        check_permission('categories');
        //validate inputs
        $this->form_validation->set_rules('name', trans("category_name"), 'required|xss_clean|max_length[200]');
        $this->form_validation->set_rules('color', trans("category_color"), 'required|xss_clean|max_length[200]');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('errors', validation_errors());
            $this->session->set_flashdata('form_data', $this->category_model->input_values());
            redirect($this->agent->referrer());
        } else {
            //category id
            $id = $this->input->post('id', true);
            $redirect_url = $this->input->post('redirect_url', true);
            if ($this->category_model->update_category($id)) {

                //update slug
                $this->category_model->update_slug($id);
                $this->session->set_flashdata('success', trans("category") . " " . trans("msg_suc_updated"));
                reset_cache_data_on_change();
                if (!empty($redirect_url)) {
                    redirect($redirect_url);
                } else {
                    redirect(admin_url() . 'categories');
                }

            } else {
                $this->session->set_flashdata('form_data', $this->category_model->input_values());
                $this->session->set_flashdata('error', trans("msg_error"));
                redirect($this->agent->referrer());
            }
        }
    }

    /**
     * Delete Category Post
     */
    public function delete_category_post()
    {
        if (!check_user_permission('categories')) {
            exit();
        }
        $id = $this->input->post('id', true);
        //check subcategories
        if (count($this->category_model->get_subcategories_by_parent_id($id)) > 0) {
            $this->session->set_flashdata('error', trans("msg_delete_subcategories"));
            exit();
        }
        //check posts
        if ($this->post_model->get_post_count_by_category($id) > 0) {
            $this->session->set_flashdata('error', trans("msg_delete_posts"));
            exit();
        }
        if ($this->category_model->delete_category($id)) {
            $this->session->set_flashdata('success', trans("category") . " " . trans("msg_suc_deleted"));
            reset_cache_data_on_change();
        } else {
            $this->session->set_flashdata('error', trans("msg_error"));
        }
    }

    //get categories by language
    public function get_parent_categories_by_lang()
    {
        $lang_id = $this->input->post('lang_id', true);
        if (!empty($lang_id)):
            $categories = $this->category_model->get_parent_categories_by_lang($lang_id);
            foreach ($categories as $item) {
                echo '<option value="' . $item->id . '">' . $item->name . '</option>';
            }
        endif;
    }
}
