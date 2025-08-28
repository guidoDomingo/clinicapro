<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Exception;

class ConsultasComponent extends Component
{
    // Patient search properties
    public $search_documento = '';
    public $search_ficha = '';
    public $search_nombre = '';
    public $patientSuggestions = [];
    public $currentPatient = null;
    public $id_persona = null;
    
    // Form properties
    public $form_type = 'general';
    public $txtmotivo = '';
    public $motivoscomunes = 'Seleccionar';
    public $visionod = '';
    public $visionoi = '';
    public $tensionod = '';
    public $tensionoi = '';
    public $formatoConsulta = 'Seleccionar';
    public $consulta_textarea = '';
    public $formatoreceta = 'Seleccionar';
    public $receta_textarea = '';
    public $txtnota = '';
    public $proximaconsulta = '';
    public $whatsapptxt = '';
    public $email = '';
    
    // Anteojos form properties
    public $od_esf = '';
    public $od_cil = '';
    public $od_eje = '';
    public $od_dnp = '';
    public $od_add = '';
    public $od_altura = '';
    public $od_nota = '';
    public $oi_esf = '';
    public $oi_cil = '';
    public $oi_eje = '';
    public $oi_dnp = '';
    public $oi_add = '';
    public $oi_altura = '';
    public $oi_nota = '';
    public $dist_interpupilar = '';
    
    // Validation rules
    protected $rules = [
        'txtmotivo' => 'required|min:3',
        'consulta_textarea' => 'required|min:10',
        'id_persona' => 'required|integer',
    ];
    
    protected $messages = [
        'txtmotivo.required' => 'El motivo de consulta es obligatorio',
        'txtmotivo.min' => 'El motivo debe tener al menos 3 caracteres',
        'consulta_textarea.required' => 'El diagnóstico es obligatorio',
        'consulta_textarea.min' => 'El diagnóstico debe tener al menos 10 caracteres',
        'id_persona.required' => 'Debe seleccionar un paciente',
    ];
    
    public function mount()
    {
        // Initialize component
        $this->patientSuggestions = [];
        $this->currentPatient = null;
    }
    
    public function searchPatients()
    {
        try {
            $this->patientSuggestions = [];
            
            // Build search query based on available criteria
            $query = DB::table('rh_person')
                ->select('person_id as id', 
                        DB::raw("CONCAT(first_name, ' ', COALESCE(last_name, '')) as nombre"),
                        'document_number as dni',
                        'record_number as ficha')
                ->where('is_active', true);
            
            $hasSearchCriteria = false;
            
            if (!empty($this->search_nombre) && strlen($this->search_nombre) >= 2) {
                $query->where(function($q) {
                    $searchTerm = '%' . $this->search_nombre . '%';
                    $q->where('first_name', 'ILIKE', $searchTerm)
                      ->orWhere('last_name', 'ILIKE', $searchTerm);
                });
                $hasSearchCriteria = true;
            }
            
            if (!empty($this->search_documento)) {
                $query->where('document_number', 'ILIKE', '%' . $this->search_documento . '%');
                $hasSearchCriteria = true;
            }
            
            if (!empty($this->search_ficha)) {
                $query->where('record_number', 'ILIKE', '%' . $this->search_ficha . '%');
                $hasSearchCriteria = true;
            }
            
            if ($hasSearchCriteria) {
                $results = $query->orderBy('first_name')
                                ->orderBy('last_name')
                                ->limit(10)
                                ->get();
                
                $this->patientSuggestions = $results->map(function($patient) {
                    return [
                        'id' => $patient->id,
                        'nombre' => trim($patient->nombre),
                        'dni' => $patient->dni ?? '',
                        'ficha' => $patient->ficha ?? 'F' . str_pad($patient->id, 3, '0', STR_PAD_LEFT)
                    ];
                })->toArray();
            }
            
        } catch (Exception $e) {
            $this->addError('search', 'Error en la búsqueda: ' . $e->getMessage());
            $this->patientSuggestions = [];
        }
    }
    
    public function selectPatient($patientId)
    {
        try {
            // Load complete patient data
            $patient = DB::table('rh_person')
                ->select(
                    'person_id as id',
                    DB::raw("CONCAT(first_name, ' ', COALESCE(last_name, '')) as nombre"),
                    'first_name',
                    'last_name',
                    'document_number as documento',
                    'email',
                    'phone_number as whatsapp',
                    'record_number as ficha',
                    'birth_date as fecha_nacimiento',
                    'address',
                    'gender'
                )
                ->where('person_id', $patientId)
                ->where('is_active', true)
                ->first();
            
            if ($patient) {
                // Set current patient data
                $this->currentPatient = [
                    'id' => $patient->id,
                    'nombre' => trim($patient->nombre),
                    'documento' => $patient->documento ?? '',
                    'ficha' => $patient->ficha ?? 'F' . str_pad($patientId, 3, '0', STR_PAD_LEFT),
                    'email' => $patient->email ?? '',
                    'whatsapp' => $patient->whatsapp ?? '',
                    'fecha_nacimiento' => $patient->fecha_nacimiento ?? '',
                    'direccion' => $patient->address ?? '',
                    'genero' => $patient->gender ?? '',
                    'total_consultas' => 0, // TODO: Calculate from consultas table
                    'cuota_mb' => 0 // TODO: Calculate if needed
                ];
                
                // Set form data
                $this->id_persona = $patient->id;
                $this->search_nombre = $this->currentPatient['nombre'];
                $this->search_documento = $this->currentPatient['documento'];
                $this->search_ficha = $this->currentPatient['ficha'];
                $this->email = $this->currentPatient['email'];
                $this->whatsapptxt = $this->currentPatient['whatsapp'];
                
                // Clear suggestions
                $this->patientSuggestions = [];
                
                // Emit event to update UI
                $this->emit('patientSelected', $this->currentPatient);
                
            } else {
                $this->addError('patient', 'Paciente no encontrado');
            }
            
        } catch (Exception $e) {
            $this->addError('patient', 'Error cargando paciente: ' . $e->getMessage());
        }
    }
    
    public function clearPatientSearch()
    {
        $this->reset([
            'search_documento',
            'search_ficha', 
            'search_nombre',
            'patientSuggestions',
            'currentPatient',
            'id_persona',
            'email',
            'whatsapptxt'
        ]);
    }
    
    public function changeFormType($type)
    {
        $this->form_type = $type;
        $this->emit('formTypeChanged', $type);
    }
    
    public function fillMotivoFromCommon()
    {
        if ($this->motivoscomunes !== 'Seleccionar') {
            $this->txtmotivo = $this->motivoscomunes;
        }
    }
    
    public function fillFromPreformat()
    {
        // TODO: Load preformat content based on formatoConsulta
        if ($this->formatoConsulta !== 'Seleccionar') {
            // Load preformat content
        }
    }
    
    public function fillRecetaFromPreformat()
    {
        // TODO: Load preformat content based on formatoreceta
        if ($this->formatoreceta !== 'Seleccionar') {
            // Load preformat content
        }
    }
    
    public function save()
    {
        try {
            $this->validate();
            
            // TODO: Implement save logic based on form_type
            $consultaData = [
                'id_persona' => $this->id_persona,
                'motivo' => $this->txtmotivo,
                'diagnostico' => $this->consulta_textarea,
                'receta' => $this->receta_textarea,
                'nota' => $this->txtnota,
                'proxima_consulta' => $this->proximaconsulta,
                'form_type' => $this->form_type,
                'created_at' => now(),
                'user_id' => session('user_id', 1)
            ];
            
            // Save based on form type
            switch ($this->form_type) {
                case 'anteojos':
                    $consultaData = array_merge($consultaData, [
                        'od_esf' => $this->od_esf,
                        'od_cil' => $this->od_cil,
                        'od_eje' => $this->od_eje,
                        'od_dnp' => $this->od_dnp,
                        'od_add' => $this->od_add,
                        'od_altura' => $this->od_altura,
                        'od_nota' => $this->od_nota,
                        'oi_esf' => $this->oi_esf,
                        'oi_cil' => $this->oi_cil,
                        'oi_eje' => $this->oi_eje,
                        'oi_dnp' => $this->oi_dnp,
                        'oi_add' => $this->oi_add,
                        'oi_altura' => $this->oi_altura,
                        'oi_nota' => $this->oi_nota,
                        'dist_interpupilar' => $this->dist_interpupilar,
                    ]);
                    break;
                    
                case 'general':
                default:
                    $consultaData = array_merge($consultaData, [
                        'vision_od' => $this->visionod,
                        'vision_oi' => $this->visionoi,
                        'tension_od' => $this->tensionod,
                        'tension_oi' => $this->tensionoi,
                    ]);
                    break;
            }
            
            // Insert into database
            $consultaId = DB::table('consultas')->insertGetId($consultaData);
            
            if ($consultaId) {
                session()->flash('message', 'Consulta guardada exitosamente');
                $this->emit('consultaSaved', $consultaId);
            } else {
                $this->addError('save', 'Error al guardar la consulta');
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors are automatically handled by Livewire
            throw $e;
        } catch (Exception $e) {
            $this->addError('save', 'Error al guardar: ' . $e->getMessage());
        }
    }
    
    public function reset()
    {
        $this->resetExcept(['currentPatient', 'id_persona', 'search_nome', 'search_documento', 'search_ficha']);
    }
    
    public function render()
    {
        return view('livewire.consultas-component');
    }
}