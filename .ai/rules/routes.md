---
paths:
  - 'routes/*.php'
---

# Routes

## Route::resource needs explicit parameter names
Route::resource() derives route parameter names via Str::singular() on the segment, which can be wrong: 'work-orders' becomes {work_order} (should be {workOrder}) and 'colaboradores' becomes {colaboradore} (should be {colaborador}). Controllers and FormRequests read these via $this->route('workOrder'), so always pin them: ->parameters(['work-orders' => 'workOrder', 'colaboradores' => 'colaborador']). Same for custom singulars like catalog => catalogItem, links => shortLink, files => document, radio-links => radioLink, form-templates => formTemplate. Verify with php artisan route:list after refactoring.
