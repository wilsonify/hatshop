#!/bin/bash

# Input file name (the source SQL file to be split)
input_file="complete.sql"

# Check if input file exists
if [ ! -f "$input_file" ]; then
  echo "Input file '$input_file' not found. Please make sure the file exists in the current directory."
  exit 1
fi

# Create a directory for the split files
output_dir="split_sql_files"
mkdir -p "$output_dir"

# Variables to track file numbering and content
file_number=0
current_file=""

# Read the input file line-by-line
while IFS= read -r line; do

  # Check if the line starts with '-- Create'
  if [[ $line =~ ^--\ Create ]]; then

    # Close the current file if it exists
    if [ -n "$current_file" ]; then
      echo "Closing file: $current_file"
    fi

    # Extract the descriptive name from the comment (remove '-- ')
    section_name=$(echo "$line" | sed 's/^-- //')

    # Replace spaces with underscores, remove special characters, and limit length
    safe_section_name=$(echo "$section_name" | tr ' ' '_' | tr -cd '[:alnum:]_')

    # Increment the file number and create a new file name
    file_number=$((file_number + 1))

    # Format the file number with leading zeros (e.g., 01, 02, 03, etc.)
    formatted_number=$(printf "%02d" $file_number)

    # Create the new output file name
    current_file="$output_dir/${formatted_number}_${safe_section_name}.sql"

    echo "Creating new file: $current_file"

    # Start writing to the new file
    echo "$line" > "$current_file"
  else
    # Write the current line to the active file, if it exists
    if [ -n "$current_file" ]; then
      echo "$line" >> "$current_file"
    fi
  fi

done < "$input_file"

echo "SQL file split complete. Files are in the '$output_dir' directory."
