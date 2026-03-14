<?php

namespace App\Livewire\Admin;

use App\Models\Staff;
use App\Repositories\StaffRepository;
use App\Services\StaffService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Staff')]
class StaffManager extends Component
{
    use WithPagination;

    public string $search='', $availabilityFilter='';
    public bool $showModal=false;
    public ?int $editingId=null, $viewingId=null;
    public string $name='', $email='', $phone='', $position='Cleaner', $shift_start='08:00', $shift_end='17:00';
    public array $working_days=['monday','tuesday','wednesday','thursday','friday'];

    protected function rules(): array
    {
        $emailRule = $this->editingId ? 'nullable' : 'required|email|unique:users,email';
        return [
            'name'        => 'required|string|max:255',
            'email'       => $emailRule,
            'phone'       => 'nullable|string|max:20',
            'position'    => 'required|string|max:100',
            'shift_start' => 'nullable|date_format:H:i',
            'shift_end'   => 'nullable|date_format:H:i',
            'working_days'=> 'array',
        ];
    }

    public function openCreate(): void { $this->resetForm(); $this->showModal=true; }

    public function openEdit(int $id): void
    {
        $staff=$this->getStaff($id);
        $this->editingId=$id;
        $this->name=$staff->user->name;
        $this->phone=$staff->phone??'';
        $this->position=$staff->position;
        $this->shift_start=$staff->shift_start??'08:00';
        $this->shift_end=$staff->shift_end??'17:00';
        $this->working_days=$staff->working_days??['monday','tuesday','wednesday','thursday','friday'];
        $this->showModal=true;
    }

    public function save(StaffService $staffService): void
    {
        $data=$this->validate();
        if ($this->editingId) {
            $staffService->update($this->getStaff($this->editingId), $data);
            $this->dispatch('notify',type:'success',message:'Staff updated.');
        } else {
            $staffService->create($data);
            $this->dispatch('notify',type:'success',message:'Staff created.');
        }
        $this->closeModal();
    }

    public function delete(int $id, StaffService $staffService): void
    {
        $staffService->delete($this->getStaff($id));
        $this->dispatch('notify',type:'success',message:'Staff deleted.');
    }

    public function closeModal(): void { $this->showModal=false; $this->editingId=null; $this->viewingId=null; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset('name','email','phone');
        $this->position='Cleaner'; $this->shift_start='08:00'; $this->shift_end='17:00';
        $this->working_days=['monday','tuesday','wednesday','thursday','friday'];
        $this->resetValidation();
    }

    private function getStaff(int $id): Staff { return Staff::with('user')->findOrFail($id); }

    public function render(StaffRepository $repo)
    {
        $staffMembers=$repo->paginate(15,['search'=>$this->search,'availability'=>$this->availabilityFilter]);
        $viewingStaff=$this->viewingId ? Staff::with(['user','bookings.items.service','reviews'])->find($this->viewingId) : null;
        return view('livewire.admin.staff-manager',compact('staffMembers','viewingStaff'));
    }
}
