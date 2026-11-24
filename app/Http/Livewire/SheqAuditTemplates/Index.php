<?php

namespace App\Http\Livewire\SheqAuditTemplates;

use App\Models\SheqAuditTemplate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];

    public $sheq_audit_template_id;
    public $name;
    public $standard;
    public $description;
    public $is_active = 1;

    protected $rules = [
        'name' => 'required',
    ];

    private function resetInputFields(){
        $this->name = "";
        $this->standard = "";
        $this->description = "";
        $this->is_active = 1;
    }

    public function store(){
        $this->validate();

        $sheq_audit_template = new SheqAuditTemplate;
        $sheq_audit_template->user_id = Auth::user()->id;
        $sheq_audit_template->name = $this->name;
        $sheq_audit_template->standard = $this->standard;
        $sheq_audit_template->description = $this->description;
        $sheq_audit_template->is_active = $this->is_active ? 1 : 0;
        $sheq_audit_template->save();

        $this->dispatchBrowserEvent('hide-sheq_audit_templateModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Audit Template Created Successfully!!"
        ]);
    }

    // Edit/delete of a template (and building its checklist) is limited to
    // its creator, is_admin users and Super Admins. Checked on every action,
    // not just by hiding the menu items.
    private function canManage($sheq_audit_template): bool
    {
        if ($sheq_audit_template && $sheq_audit_template->canBeBuiltBy(Auth::user())) {
            return true;
        }
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Only the creator of this template or a Super Admin can edit or delete it."
        ]);
        return false;
    }

    public function edit($id){
        $sheq_audit_template = SheqAuditTemplate::find($id);
        if (!$this->canManage($sheq_audit_template)) return;
        $this->sheq_audit_template_id = $sheq_audit_template->id;
        $this->name = $sheq_audit_template->name;
        $this->standard = $sheq_audit_template->standard;
        $this->description = $sheq_audit_template->description;
        $this->is_active = $sheq_audit_template->is_active;
        $this->dispatchBrowserEvent('show-sheq_audit_templateEditModal');
    }

    public function update(){
        $this->validate();

        $sheq_audit_template = SheqAuditTemplate::find($this->sheq_audit_template_id);
        if (!$this->canManage($sheq_audit_template)) return;
        $sheq_audit_template->name = $this->name;
        $sheq_audit_template->standard = $this->standard;
        $sheq_audit_template->description = $this->description;
        $sheq_audit_template->is_active = $this->is_active ? 1 : 0;
        $sheq_audit_template->update();

        $this->dispatchBrowserEvent('hide-sheq_audit_templateEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Audit Template Updated Successfully!!"
        ]);
    }

    public function delete($id){
        if (!$this->canManage(SheqAuditTemplate::find($id))) return;
        $this->sheq_audit_template_id = $id;
        $this->dispatchBrowserEvent('show-sheq_audit_templateDeleteModal');
    }

    public function destroy(){
        $sheq_audit_template = SheqAuditTemplate::find($this->sheq_audit_template_id);
        if (!$this->canManage($sheq_audit_template)) return;
        if ($sheq_audit_template) {
            $sheq_audit_template->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_audit_templateDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Audit Template Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqAuditTemplate::query()->withCount('sections');
        if ($this->search) {
            $query->where('name','like','%'.$this->search.'%');
        }
        $sheq_audit_templates = $query->orderBy('name','asc')->paginate(10);

        $user = Auth::user();

        return view('livewire.sheq-audit-templates.index',[
            'sheq_audit_templates' => $sheq_audit_templates,
            'is_privileged' => $user && ($user->is_admin() || $user->isSuperAdmin()),
        ]);
    }
}
