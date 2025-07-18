#!/usr/bin/env python3
"""
Verifica el equilibrio de llaves en el archivo PHP
"""

def check_braces(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        lines = f.readlines()
    
    brace_count = 0
    in_string = False
    escape_next = False
    string_char = None
    
    for line_num, line in enumerate(lines, 1):
        if line_num >= 530 and line_num <= 870:  # Rango de la función problematica
            for i, char in enumerate(line):
                if escape_next:
                    escape_next = False
                    continue
                    
                if char == '\\' and in_string:
                    escape_next = True
                    continue
                
                if char in ['"', "'"] and not in_string:
                    in_string = True
                    string_char = char
                elif char == string_char and in_string:
                    in_string = False
                    string_char = None
                elif not in_string:
                    if char == '{':
                        brace_count += 1
                        print(f"Línea {line_num}: Abre llave, total: {brace_count}")
                    elif char == '}':
                        brace_count -= 1
                        print(f"Línea {line_num}: Cierra llave, total: {brace_count}")
                        
                        if brace_count < 0:
                            print(f"ERROR: Más llaves de cierre que de apertura en línea {line_num}")
                            return False
    
    print(f"Balance final de llaves: {brace_count}")
    return brace_count == 0

if __name__ == "__main__":
    file_path = "c:/laragon/www/clinica/model/servicios.model.php"
    result = check_braces(file_path)
    print("Llaves balanceadas:" if result else "Llaves DESBALANCEADAS")
