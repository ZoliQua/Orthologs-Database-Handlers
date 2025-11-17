# Ortholog Database Handlers

A collection of PHP-based bioinformatics tools for managing, cleaning, and analyzing ortholog data across multiple model organisms. These tools were developed as part of ortholog research to process and visualize protein orthology relationships from various databases.

## Project Structure

### `local-mysql-manager-2014/`
Initial MySQL database management interface. Web-based GUI for querying, creating, and managing ortholog database tables. Supports batch operations with tab-separated data files and `.inc` table definitions.

### `local-mysql-manager-2015/`
Evolved database manager with improved features:
- **mysqli** support (replacing deprecated mysql extension)
- Modular code with separate includes
- CSV format support with bidirectional ortholog mappings
- Table protection via `Restriction` class
- jQuery DataTables integration for visualization

### `ortholog_list_cleaner/`
Data cleaning and merging pipeline for ortholog lists:
- Merges ortholog data with **PubMed** publication counts
- Integrates **UniProt** protein review status (reviewed vs. unreviewed)
- Processes gene IDs (SGD, UniProt) across species
- Outputs cleaned, annotated CSV files

### `uniprotid_cleaner/`
UniProt ID consolidation tool:
- Groups proteins by UniProt ID
- Identifies reviewed vs. unreviewed entries
- Merges unreviewed proteins into their reviewed equivalents
- Outputs cleaned mapping files

### `venn_diagram/`
Comprehensive comparative genomics analysis and visualization suite:
- **Multi-set Venn diagrams** (2–7 species) with SVG output
- Two analysis modes: `sum` (ortholog counts) and `real` (true set intersections)
- MySQL query engine for ortholog database retrieval
- **Gene Ontology (GO)** term analysis (5 GO categories)
- SVG-to-PNG conversion via LibRSVG

## Supported Organisms

| Code | Organism |
|------|----------|
| AT | *Arabidopsis thaliana* |
| CE | *Caenorhabditis elegans* |
| DM | *Drosophila melanogaster* |
| DR | *Danio rerio* |
| HS | *Homo sapiens* |
| SC | *Saccharomyces cerevisiae* |
| SP | *Schizosaccharomyces pombe* |

## Ortholog Data Sources

- **InParanoid** — sequence-based ortholog detection
- **KOG** — Clusters of Orthologous Groups for eukaryotes
- **eggNOG** — evolutionary genealogy of genes: Non-supervised Orthologous Groups
- **PomBase** — *S. pombe* genome database
- **HomoloGene** — NCBI homolog database
- **OrthoMCL** — ortholog groups via Markov clustering

## Technologies

- **PHP 5.x/7.x** — all data processing, web interfaces, and analysis scripts
- **MySQL/MySQLi** — database backend
- **SVG** — Venn diagram visualization
- **jQuery / DataTables** — web UI
- **LibRSVG** — SVG to PNG conversion
- **Git LFS** — large data file storage (CSV, TSV, TXT)

## Data Flow

```
Raw Ortholog Databases (InParanoid, KOG, eggNOG, etc.)
        ↓
  [query.php] → Merge databases per species
        ↓
  Per-species merged CSV files
        ↓
  [analyzer.php] → Venn diagram permutation analysis
        ↓
  SVG output with region counts → PNG conversion
```

## Author

**Zoltan Dul** — 2012–2017

## License

All rights reserved.
