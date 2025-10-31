<?php
/**
 * UI Components Class
 * Provides reusable UI components for consistent design
 */

class UIComponents {
    
    /**
     * Generate alert component
     */
    public static function alert($message, $type = 'info', $dismissible = true) {
        $typeClass = [
            'success' => 'alert-success',
            'danger' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ][$type] ?? 'alert-info';
        
        $dismissibleAttr = $dismissible ? 'alert-dismissible fade show' : '';
        $dismissibleBtn = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' : '';
        
        return "
        <div class='alert $typeClass $dismissibleAttr' role='alert'>
            $message
            $dismissibleBtn
        </div>";
    }
    
    /**
     * Generate loading spinner
     */
    public static function loadingSpinner($size = 'sm', $text = 'جاري التحميل...') {
        $sizeClass = [
            'sm' => 'spinner-border-sm',
            'lg' => 'spinner-border-lg'
        ][$size] ?? '';
        
        return "
        <div class='d-flex justify-content-center align-items-center p-3'>
            <div class='spinner-border $sizeClass text-primary' role='status'>
                <span class='visually-hidden'>$text</span>
            </div>
            <span class='ms-2'>$text</span>
        </div>";
    }
    
    /**
     * Generate pagination
     */
    public static function pagination($currentPage, $totalPages, $baseUrl) {
        if ($totalPages <= 1) return '';
        
        $html = '<nav aria-label="صفحات النتائج"><ul class="pagination justify-content-center">';
        
        // Previous button
        if ($currentPage > 1) {
            $prevPage = $currentPage - 1;
            $html .= "<li class='page-item'><a class='page-link' href='$baseUrl?page=$prevPage'>السابق</a></li>";
        }
        
        // Page numbers
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            $activeClass = $i == $currentPage ? 'active' : '';
            $html .= "<li class='page-item $activeClass'><a class='page-link' href='$baseUrl?page=$i'>$i</a></li>";
        }
        
        // Next button
        if ($currentPage < $totalPages) {
            $nextPage = $currentPage + 1;
            $html .= "<li class='page-item'><a class='page-link' href='$baseUrl?page=$nextPage'>التالي</a></li>";
        }
        
        $html .= '</ul></nav>';
        return $html;
    }
    
    /**
     * Generate search form
     */
    public static function searchForm($placeholder = 'ابحث...', $action = '', $method = 'GET') {
        return "
        <form method='$method' action='$action' class='d-flex mb-3'>
            <input type='text' class='form-control' name='search' placeholder='$placeholder' value='" . ($_GET['search'] ?? '') . "'>
            <button type='submit' class='btn btn-outline-primary ms-2'>بحث</button>
        </form>";
    }
    
    /**
     * Generate data table
     */
    public static function dataTable($headers, $data, $actions = []) {
        $html = '<div class="table-responsive"><table class="table table-striped table-hover">';
        
        // Headers
        $html .= '<thead class="table-dark"><tr>';
        foreach ($headers as $header) {
            $html .= "<th>$header</th>";
        }
        if (!empty($actions)) {
            $html .= '<th>العمليات</th>';
        }
        $html .= '</tr></thead>';
        
        // Body
        $html .= '<tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                if ($key !== 'id' && $key !== 'NO') {
                    $html .= "<td>" . htmlspecialchars($value) . "</td>";
                }
            }
            
            // Actions
            if (!empty($actions)) {
                $html .= '<td>';
                foreach ($actions as $action) {
                    $url = str_replace('{id}', $row['id'] ?? $row['NO'], $action['url']);
                    $class = $action['class'] ?? 'btn btn-sm';
                    $html .= "<a href='$url' class='$class'>" . $action['text'] . "</a> ";
                }
                $html .= '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
        
        return $html;
    }
    
    /**
     * Generate form field
     */
    public static function formField($name, $label, $type = 'text', $value = '', $required = false, $options = []) {
        $requiredAttr = $required ? 'required' : '';
        $class = $options['class'] ?? 'form-control';
        $placeholder = $options['placeholder'] ?? '';
        $helpText = $options['help'] ?? '';
        
        $html = "<div class='mb-3'>";
        $html .= "<label for='$name' class='form-label'>$label</label>";
        
        if ($type === 'textarea') {
            $html .= "<textarea name='$name' id='$name' class='$class' placeholder='$placeholder' $requiredAttr>$value</textarea>";
        } elseif ($type === 'select') {
            $html .= "<select name='$name' id='$name' class='$class' $requiredAttr>";
            foreach ($options['options'] as $optionValue => $optionText) {
                $selected = $value == $optionValue ? 'selected' : '';
                $html .= "<option value='$optionValue' $selected>$optionText</option>";
            }
            $html .= "</select>";
        } else {
            $html .= "<input type='$type' name='$name' id='$name' class='$class' value='" . htmlspecialchars($value) . "' placeholder='$placeholder' $requiredAttr>";
        }
        
        if ($helpText) {
            $html .= "<div class='form-text'>$helpText</div>";
        }
        
        $html .= "</div>";
        return $html;
    }
    
    /**
     * Generate card component
     */
    public static function card($title, $content, $footer = '', $class = '') {
        return "
        <div class='card $class'>
            <div class='card-header'>
                <h5 class='card-title mb-0'>$title</h5>
            </div>
            <div class='card-body'>
                $content
            </div>
            " . ($footer ? "<div class='card-footer'>$footer</div>" : '') . "
        </div>";
    }
    
    /**
     * Generate stats card
     */
    public static function statsCard($title, $value, $icon = '', $color = 'primary', $trend = '') {
        $iconHtml = $icon ? "<i class='$icon fa-2x text-$color'></i>" : '';
        $trendHtml = $trend ? "<small class='text-success'>$trend</small>" : '';
        
        return "
        <div class='card border-0 shadow-sm'>
            <div class='card-body'>
                <div class='d-flex justify-content-between align-items-center'>
                    <div>
                        <h6 class='card-title text-muted mb-1'>$title</h6>
                        <h3 class='mb-0'>$value</h3>
                        $trendHtml
                    </div>
                    $iconHtml
                </div>
            </div>
        </div>";
    }
    
    /**
     * Generate modal
     */
    public static function modal($id, $title, $content, $footer = '') {
        return "
        <div class='modal fade' id='$id' tabindex='-1'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title'>$title</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                    </div>
                    <div class='modal-body'>
                        $content
                    </div>
                    " . ($footer ? "<div class='modal-footer'>$footer</div>" : '') . "
                </div>
            </div>
        </div>";
    }
    
    /**
     * Generate breadcrumb
     */
    public static function breadcrumb($items) {
        $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
        
        foreach ($items as $index => $item) {
            $isLast = $index === count($items) - 1;
            $class = $isLast ? 'breadcrumb-item active' : 'breadcrumb-item';
            $link = $isLast ? $item['text'] : "<a href='{$item['url']}'>{$item['text']}</a>";
            $html .= "<li class='$class'>$link</li>";
        }
        
        $html .= '</ol></nav>';
        return $html;
    }
    
    /**
     * Generate progress bar
     */
    public static function progressBar($percentage, $label = '', $color = 'primary') {
        return "
        <div class='progress mb-2'>
            <div class='progress-bar bg-$color' role='progressbar' style='width: $percentage%' aria-valuenow='$percentage' aria-valuemin='0' aria-valuemax='100'>
                $label
            </div>
        </div>";
    }
}
