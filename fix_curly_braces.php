<?php
/**
 * Script to fix S121 - Add curly braces to if/else/elseif statements
 * 
 * This script finds patterns like:
 *   if (condition)
 *     single_statement;
 * 
 * And converts them to:
 *   if (condition) {
 *     single_statement;
 *   }
 */

function fixCurlyBraces($content) {
    $lines = explode("\n", $content);
    $result = [];
    $i = 0;
    $modified = false;
    
    while ($i < count($lines)) {
        $line = $lines[$i];
        
        // Check if this line is an if/elseif/else without a brace at the end
        // Pattern: if (...) followed by no { at the end
        if (preg_match('/^(\s*)(if|elseif)\s*\(.+\)\s*$/', $line, $matches)) {
            $indent = $matches[1];
            
            // Check if next line exists and is a single statement (not starting with { and not another control structure)
            if ($i + 1 < count($lines)) {
                $nextLine = $lines[$i + 1];
                $trimmedNext = ltrim($nextLine);
                
                // If next line doesn't start with { and is not empty
                if (!preg_match('/^\s*\{/', $nextLine) && trim($nextLine) !== '' && 
                    !preg_match('/^\s*(if|else|elseif|for|foreach|while|switch|try|catch|finally)\s*[\(\{]/', $nextLine)) {
                    
                    // Add opening brace
                    $result[] = $line . ' {';
                    $result[] = $nextLine;
                    
                    // Check if next is else/elseif
                    if ($i + 2 < count($lines)) {
                        $lineAfter = $lines[$i + 2];
                        if (preg_match('/^\s*else\s*$/', $lineAfter) || preg_match('/^\s*elseif\s*\(/', $lineAfter)) {
                            $result[] = $indent . '} ' . trim($lineAfter);
                            $i += 2;
                        } else {
                            $result[] = $indent . '}';
                            $i += 1;
                        }
                    } else {
                        $result[] = $indent . '}';
                        $i += 1;
                    }
                    $modified = true;
                    $i++;
                    continue;
                }
            }
        }
        // Handle standalone else without brace
        elseif (preg_match('/^(\s*)else\s*$/', $line, $matches)) {
            $indent = $matches[1];
            
            if ($i + 1 < count($lines)) {
                $nextLine = $lines[$i + 1];
                
                // If next line doesn't start with {
                if (!preg_match('/^\s*\{/', $nextLine) && trim($nextLine) !== '' &&
                    !preg_match('/^\s*(if|else|elseif|for|foreach|while|switch|try|catch|finally)\s*[\(\{]/', $nextLine)) {
                    
                    $result[] = $line . ' {';
                    $result[] = $nextLine;
                    $result[] = $indent . '}';
                    $modified = true;
                    $i += 2;
                    continue;
                }
            }
        }
        
        $result[] = $line;
        $i++;
    }
    
    return ['content' => implode("\n", $result), 'modified' => $modified];
}

// Find PHP files
$directories = [
    'c04 - Creating the Product Catalog Part II',
    'c05 - Searching the Catalog', 
    'c06 - Receiving Payments Using PayPal',
    'c07 - Catalog Administration',
    'c08 - The Shopping Cart',
    'c09 - Dealing with Customer Orders',
    'c10 - Product Recommendations',
    'c11 - Managing Customer Details',
    'c12 - Storing Customer Orders',
    'c13 - Implementing the Order Pipeline Part I',
    'c14 - Implementing the Order Pipeline Part II',
    'c15 - Credit Card Transactions - Authorize.net',
    'c15 - Credit Card Transactions - Datacash.com',
    'c16 - Product Reviews - Authorize.net',
    'c16 - Product Reviews - Datacash.com',
    'c17 - Connecting to Web Services - Authorize.net',
    'c17 - Connecting to Web Services - Datacash.com'
];

$totalFixed = 0;

foreach ($directories as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) {
        echo "Directory not found: $dir\n";
        continue;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getPathname();
            
            // Skip vendor directories
            if (strpos($filePath, '/vendor/') !== false) {
                continue;
            }
            
            $content = file_get_contents($filePath);
            $result = fixCurlyBraces($content);
            
            if ($result['modified']) {
                file_put_contents($filePath, $result['content']);
                echo "Fixed: $filePath\n";
                $totalFixed++;
            }
        }
    }
}

echo "\nTotal files fixed: $totalFixed\n";
