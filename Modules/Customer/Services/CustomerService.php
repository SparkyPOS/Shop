<?php

namespace Modules\Customer\Services;
use \Modules\Customer\Repositories\CustomerRepository;
use App\Models\User;
use App\Traits\ImageStore;

class CustomerService
{
    use ImageStore;
    protected $customerRepository;

    public function __construct(CustomerRepository  $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function getAll()
    {
        return $this->customerRepository->getAll();
    }

    public function find($id)
    {
        return $this->customerRepository->find($id);
    }

    public function store($data){
        if (!empty($data['photo'])) {
            // save as plain path string for compatibility with showImage/display
            $photo = $this->saveImage($data['photo'], 165, 165);
            $data['avatar'] = $photo;
        }
        return $this->customerRepository->store($data);
    }

    public function update($data, $id){
        if (!empty($data['photo'])) {
            $user = User::find($id);
            if ($user && $user->avatar) {
                $this->deleteImage($user->avatar);
            }
            $photo = $this->saveImage($data['photo'], 165, 165);
            $data['avatar'] = $photo;
        }
        return $this->customerRepository->update($data, $id);
    }

    public function destroy($id){
        return $this->customerRepository->destroy($id);
    }
    public function imageDelete($data){
        return $this->customerRepository->imageDelete($data);
    }
    public function BulkUploadStore($data){
        return $this->customerRepository->BulkUploadStore($data);
    }

    public function posCustomer()
    {
        return $this->customerRepository->posCustomer();
    }

    public function getCustomersByAjax($search){
        return $this->customerRepository->getCustomersByAjax($search);
    }


}
