# QGate Database Migration Documentation

This document details the migration from using external APIs to direct SQL Server database access for the QGate application.

## Connection Details

```php
$serverName = "DCSQLSRV03.bps.go.id"; // or IP "10.0.77.23"
$connectionOptions = [
    "Database" => "QG_PROD",
    "Uid" => "qgreadonly",
    "PWD" => "A!rNeoHa55"
];
```

## Data Flow Overview

The original system used REST API calls to fetch data from the QGate system. The new implementation replaces these API calls with direct SQL Server queries to the QG_PROD database. The flow is as follows:

1. Request comes to `api.php` with specific action parameter
2. SQL connection is established to the QG_PROD database
3. SQL query is executed based on the requested action
4. Results are formatted to match the original API response format
5. JSON response is returned to the client

## Table and Column Mapping

Below is a detailed mapping of database tables and their columns to the corresponding API endpoints:

### 1. Projects (`fetchProjects`)

**Table**: `projects`
**Key Columns**:
- `id` - Project ID
- `year` - Project year
- `name` - Project name
- `description` - Project description
- `is_deleted` - Deletion flag

**SQL Query**:
```sql
SELECT * FROM [projects] WHERE [year] = ? AND [is_deleted] IS NULL ORDER BY [id]
```

### 2. Gates (`fetchGates`)

**Table**: `project_gates`
**Key Columns**:
- `id` - Gate ID
- `id_project` - Project ID reference
- `gate_number` - Gate number
- `gate_name` - Gate name
- `evaluation_start` - Evaluation start date
- `evaluation_end` - Evaluation end date
- `is_deleted` - Deletion flag

**SQL Query**:
```sql
SELECT * FROM [project_gates] WHERE [id_project] = ? AND [is_deleted] IS NULL ORDER BY [gate_number]
```

### 3. Measurements (`fetchMeasurements`)

**Table**: `project_measurements`
**Key Columns**:
- `id` - Measurement ID
- `id_project` - Project ID reference
- `id_gate` - Gate ID reference
- `measurement_number` - Measurement number
- `measurement_name` - Measurement name
- `measurement_description` - Measurement description
- `green_condition` - Green condition description
- `red_condition` - Red condition description
- `yellow_condition` - Yellow condition description
- `is_deleted` - Deletion flag

**SQL Query**:
```sql
SELECT * FROM [project_measurements] 
WHERE [id_project] = ? AND [id_gate] = ? 
AND [is_deleted] IS NULL 
ORDER BY [measurement_number]
```

### 4. Assessments (`fetchAssessments`)

**Table**: `project_assessments`
**Key Columns**:
- `year` - Assessment year
- `prov` - Province code
- `kab` - Kabupaten code
- `id_project` - Project ID reference
- `id_gate` - Gate ID reference
- `assessment` - Assessment data (JSON string)
- `state` - Assessment state
- `notes` - Assessment notes (JSON string)

**SQL Query**:
```sql
SELECT * FROM [project_assessments] 
WHERE [id_project] = ? AND [id_gate] = ? 
AND [prov] = ? AND [kab] = ?
```

**Special Processing**:
- The `assessment` field is JSON decoded
- The `notes` field is JSON decoded
- The `state` field is duplicated as `status` to maintain compatibility with the old API

### 5. Preventives by Kabupaten (`fetchPreventivesByKab`)

**Table**: `project_preventives`
**Key Columns**:
- `id` - Preventive action ID
- `year` - Preventive year
- `id_project` - Project ID reference
- `id_gate` - Gate ID reference
- `id_measurement` - Measurement ID reference
- `prov` - Province code
- `kab` - Kabupaten code
- `index_action` - Action index
- `aksi` - Action description
- `is_deleted` - Deletion flag

**SQL Query**:
```sql
SELECT * FROM [project_preventives] 
WHERE [year] = ? AND [id_project] = ? AND [id_gate] = ? 
AND [id_measurement] = ? AND [prov] = ? AND [kab] = ? 
AND [is_deleted] IS NULL 
ORDER BY [index_action]
```

### 6. Preventives by Measurement (`fetchPreventivesByMeasurement`)

**Table**: `project_preventives`
**Key Columns**: Same as above

**SQL Query**:
```sql
SELECT * FROM [project_preventives] 
WHERE [year] = ? AND [id_project] = ? AND [id_gate] = ? 
AND [id_measurement] = ? 
AND [is_deleted] IS NULL 
ORDER BY [prov], [kab], [index_action]
```

### 7. Correctives by Kabupaten (`fetchCorrectivesByKab`)

**Table**: `project_correctives`
**Key Columns**:
- `id` - Corrective action ID
- `year` - Corrective year
- `id_project` - Project ID reference
- `id_gate` - Gate ID reference
- `id_measurement` - Measurement ID reference
- `assessment` - Assessment value
- `prov` - Province code
- `kab` - Kabupaten code
- `index_action` - Action index
- `aksi` - Action description
- `is_deleted` - Deletion flag

**SQL Query**:
```sql
SELECT * FROM [project_correctives] 
WHERE [year] = ? AND [id_project] = ? AND [id_gate] = ? 
AND [id_measurement] = ? AND [prov] = ? AND [kab] = ? 
AND [is_deleted] IS NULL 
ORDER BY [index_action]
```

### 8. Correctives by Measurement (`fetchCorrectivesByMeasurement`)

**Table**: `project_correctives`
**Key Columns**: Same as above

**SQL Query**:
```sql
SELECT * FROM [project_correctives] 
WHERE [year] = ? AND [id_project] = ? AND [id_gate] = ? 
AND [id_measurement] = ? 
AND [is_deleted] IS NULL 
ORDER BY [prov], [kab], [index_action]
```

### 9. Project Details (`fetchProjectSpesific`)

**Table**: `projects`
**Key Columns**: Same as Projects

**SQL Query**:
```sql
SELECT * FROM [projects] WHERE [year] = ? AND [id] = ? AND [is_deleted] IS NULL
```

### 10. Coverages (`fetchCoverages`)

**Table**: `project_coverages`
**Key Columns**:
- `id_project` - Project ID reference
- `prov` - Province code
- `kab` - Kabupaten code
- `name` - Coverage name

**SQL Query**:
```sql
SELECT * FROM [project_coverages] WHERE [id_project] = ? ORDER BY [prov], [kab]
```

### 11. Need Correctives (`fetchNeedCorrectives`)

**Tables**: `project_assessments`, `project_measurements`
**Process**:
1. Fetch assessment data for the specified project, gate, province, and kabupaten
2. Parse the assessment JSON to identify measurements with red (1) or yellow (2) assessments
3. Fetch the corresponding measurement details for those requiring corrective actions

### 12. All Actions (`fetchAllActions`)

**Tables**: `project_assessments`, `project_measurements`
**Process**:
1. Fetch assessment data for the specified project, gate, province, and kabupaten
2. Fetch all measurements for the project and gate
3. Parse the assessment JSON to get assessment values for each measurement
4. For each measurement, determine the appropriate actions based on assessment value:
   - Red (1): Fetch corrective actions
   - Yellow (2): Fetch yellow actions
   - Green (3): Fetch preventive actions
5. Return all relevant actions with their corresponding measurements

## Key Differences from API Implementation

### 1. Direct Data Access
The new implementation accesses the database directly, eliminating the network overhead of API calls.

### 2. Status Field Handling
The old API used a `status` field, while the database stores this as `state`. The new implementation maps `state` to `status` to maintain compatibility.

### 3. JSON Processing
Several fields (`assessment`, `notes`, etc.) are stored as JSON strings in the database. The new implementation parses these strings into proper JSON objects before returning them.

### 4. Datetime Formatting
SQL Server returns DateTime objects for date fields. These are converted to formatted strings for JSON output.

### 5. Complex Queries
For endpoints like `fetchNeedCorrectives` and `fetchAllActions`, multiple database queries are executed and their results are combined, replacing the complex logic that was previously handled by the API server.

## API Mapping to SQL Server Tables

| API Endpoint | Database Tables | Key Parameters | Special Processing |
|--------------|-----------------|----------------|-------------------|
| fetchProjects | projects | year | Filter by is_deleted IS NULL |
| fetchGates | project_gates | id_project | Filter by is_deleted IS NULL |
| fetchMeasurements | project_measurements | id_project, id_gate | Filter by is_deleted IS NULL |
| fetchAssessments | project_assessments | id_project, id_gate, prov, kab | JSON decode assessment and notes, add status field |
| fetchPreventivesByKab | project_preventives | year, id_project, id_gate, id_measurement, prov, kab | Filter by is_deleted IS NULL |
| fetchPreventivesByMeasurement | project_preventives | year, id_project, id_gate, id_measurement | Filter by is_deleted IS NULL, order by prov, kab |
| fetchCorrectivesByKab | project_correctives | year, id_project, id_gate, id_measurement, prov, kab | Filter by is_deleted IS NULL |
| fetchCorrectivesByMeasurement | project_correctives | year, id_project, id_gate, id_measurement | Filter by is_deleted IS NULL, order by prov, kab |
| fetchProjectSpesific | projects | year, project_id | Filter by is_deleted IS NULL |
| fetchCoverages | project_coverages | id_project | Order by prov, kab |
| fetchNeedCorrectives | project_assessments, project_measurements | id_project, id_gate, prov, kab, year | Complex processing for assessment data |
| fetchAllActions | project_assessments, project_measurements | id_project, id_gate, prov, kab | Complex processing for action determination | 