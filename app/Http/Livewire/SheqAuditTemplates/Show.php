<?php

namespace App\Http\Livewire\SheqAuditTemplates;

use App\Models\SheqAuditItem;
use App\Models\SheqAuditSection;
use App\Models\SheqAuditTemplate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public $sheq_audit_template_id;

    public $sheq_audit_section_id;
    public $section_code;
    public $section_title;
    public $section_sort_order = 0;

    public $sheq_audit_item_id;
    public $item_section_id;
    public $item_code;
    public $requirement;
    public $guidance;
    public $possible_mark = 0;
    public $item_sort_order = 0;

    // 'view' = read-only checklist for everyone; 'build' = add/edit/delete,
    // which is only honoured for the creator / is_admin / Super Admin.
    public $mode = 'view';

    public function mount($sheq_audit_template_id, $mode = 'view'){
        $this->sheq_audit_template_id = $sheq_audit_template_id;
        $this->mode = $mode;
    }

    // Livewire only makes follow-up requests for build actions (the read-only
    // page has no wire:click), so every one of them must come from a builder.
    // Checked server-side, never from the public $mode property.
    public function hydrate(){
        abort_unless($this->canBuild(), 403, 'Only the creator of this template or a Super Admin can build its checklist.');
    }

    private function canBuild(): bool
    {
        $template = SheqAuditTemplate::find($this->sheq_audit_template_id);
        return $template && $template->canBeBuiltBy(Auth::user());
    }

    private function resetSectionFields(){
        $this->sheq_audit_section_id = "";
        $this->section_code = "";
        $this->section_title = "";
        $this->section_sort_order = 0;
    }

    private function resetItemFields(){
        $this->sheq_audit_item_id = "";
        $this->item_section_id = "";
        $this->item_code = "";
        $this->requirement = "";
        $this->guidance = "";
        $this->possible_mark = 0;
        $this->item_sort_order = 0;
    }

    public function storeSection(){
        $this->validate([
            'section_title' => 'required',
        ]);

        $section = new SheqAuditSection;
        $section->sheq_audit_template_id = $this->sheq_audit_template_id;
        $section->code = $this->section_code;
        $section->title = $this->section_title;
        $section->sort_order = $this->section_sort_order ?: 0;
        $section->save();

        $this->dispatchBrowserEvent('hide-sectionModal');
        $this->resetSectionFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Section Created Successfully!!"
        ]);
    }

    public function editSection($id){
        $section = SheqAuditSection::find($id);
        $this->sheq_audit_section_id = $section->id;
        $this->section_code = $section->code;
        $this->section_title = $section->title;
        $this->section_sort_order = $section->sort_order;
        $this->dispatchBrowserEvent('show-sectionEditModal');
    }

    public function updateSection(){
        $this->validate([
            'section_title' => 'required',
        ]);

        $section = SheqAuditSection::find($this->sheq_audit_section_id);
        $section->code = $this->section_code;
        $section->title = $this->section_title;
        $section->sort_order = $this->section_sort_order ?: 0;
        $section->update();

        $this->dispatchBrowserEvent('hide-sectionEditModal');
        $this->resetSectionFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Section Updated Successfully!!"
        ]);
    }

    public function deleteSection($id){
        $this->sheq_audit_section_id = $id;
        $this->dispatchBrowserEvent('show-sectionDeleteModal');
    }

    public function destroySection(){
        $section = SheqAuditSection::find($this->sheq_audit_section_id);
        if ($section) {
            foreach ($section->items as $item) {
                $item->delete();
            }
            $section->delete();
        }
        $this->dispatchBrowserEvent('hide-sectionDeleteModal');
        $this->resetSectionFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Section Deleted Successfully!!"
        ]);
    }

    public function addItem($sectionId){
        $this->resetItemFields();
        $this->item_section_id = $sectionId;
        $this->dispatchBrowserEvent('show-itemModal');
    }

    public function storeItem(){
        $this->validate([
            'requirement' => 'required',
            'possible_mark' => 'required|integer|min:0',
        ]);

        $item = new SheqAuditItem;
        $item->sheq_audit_section_id = $this->item_section_id;
        $item->code = $this->item_code;
        $item->requirement = $this->requirement;
        $item->guidance = $this->guidance;
        $item->possible_mark = $this->possible_mark ?: 0;
        $item->sort_order = $this->item_sort_order ?: 0;
        $item->save();

        $this->dispatchBrowserEvent('hide-itemModal');
        $this->resetItemFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Requirement Created Successfully!!"
        ]);
    }

    public function editItem($id){
        $item = SheqAuditItem::find($id);
        $this->sheq_audit_item_id = $item->id;
        $this->item_section_id = $item->sheq_audit_section_id;
        $this->item_code = $item->code;
        $this->requirement = $item->requirement;
        $this->guidance = $item->guidance;
        $this->possible_mark = $item->possible_mark;
        $this->item_sort_order = $item->sort_order;
        $this->dispatchBrowserEvent('show-itemEditModal');
    }

    public function updateItem(){
        $this->validate([
            'requirement' => 'required',
            'possible_mark' => 'required|integer|min:0',
        ]);

        $item = SheqAuditItem::find($this->sheq_audit_item_id);
        $item->sheq_audit_section_id = $this->item_section_id;
        $item->code = $this->item_code;
        $item->requirement = $this->requirement;
        $item->guidance = $this->guidance;
        $item->possible_mark = $this->possible_mark ?: 0;
        $item->sort_order = $this->item_sort_order ?: 0;
        $item->update();

        $this->dispatchBrowserEvent('hide-itemEditModal');
        $this->resetItemFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Requirement Updated Successfully!!"
        ]);
    }

    public function deleteItem($id){
        $this->sheq_audit_item_id = $id;
        $this->dispatchBrowserEvent('show-itemDeleteModal');
    }

    public function destroyItem(){
        $item = SheqAuditItem::find($this->sheq_audit_item_id);
        if ($item) {
            $item->delete();
        }
        $this->dispatchBrowserEvent('hide-itemDeleteModal');
        $this->resetItemFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Requirement Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $sheq_audit_template = SheqAuditTemplate::with('sections.items')->find($this->sheq_audit_template_id);

        return view('livewire.sheq-audit-templates.show',[
            'sheq_audit_template' => $sheq_audit_template,
            'read_only' => $this->mode !== 'build' || !$this->canBuild(),
        ]);
    }
}
