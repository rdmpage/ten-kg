# Ten KG

Simple knowledge graph based on ten year library project.

## Configuration

For each data source we create a configuration file in YAML. Each configuration file is processed to retrieve the RDF, which we then upload to a triple store. The config files are a bit like the [HCLS VoID](https://www.w3.org/TR/hcls-dataset/) files used by the [Linked Open Data platform for EBI data](https://www.ebi.ac.uk/rdf/), but are simpler and are modelled on a [Bioschemas dataset](https://bioschemas.org/profiles/Dataset).
This file is of the form:

```yaml
name: <name of the dataset>
identifier: <citable identifier>
url: <URL for dataset, e.g. its home page>
    
distribution:
    contentUrl: <URL for retrieving data>
    encodingFormat: <MIME type of data file>
```

Field | Description
--- | ---
name | A simple, human readable name for the dataset.
identifier | A citable identifier for the data, such as a DOI if the data is hosted in a repository. 
url | A URL for the dataset, which will be used to identify the `named graph` for that dataset within the triple store. Typically something simple such as the home page for the data, e.g. `https://orcid.org`. 
distribution | This field has two subfields (`contentUrl` and `encodingFormat`) that describe where to get the data and in what format.
contentUrl | URL where we can retrieve the data. This should be a direct link to a downloadable file, not an indirect link such as a DOI. If data is being loaded from a local file then use the `file://` prefix followed by the full path to the file. 
encodingFormat | MIME type for the data retrieved from `encodingFormat`, for example `application/n-triples` WE use this value to tell the triple store how the linked data is encoded.

Note that `contentUrl` supports loading from local files. This is to enable quick and easy testing of examples. Ideally data loaded for production would be freely available from external repositories.

## Loading data

The script `upload.php` will read one or more configuration files, retrieve the data, convert it into smaller chunks if needed, the upload it to the triple store. By default each dataset is loaded into its own named graph, and any data already stored under that graph is deleted.

The script `upload.php` adds data to one or more named graphs in the triple store. If you want a clean start use `upload-and-replace.php` which first deletes all triple sin the named graph then uploads the new data.


### Triple stores

Triple stores such as [Oxigraph](https://crates.io/crates/oxigraph_server) and [Blazegraph](https://blazegraph.com) differ in their interfaces for adding data, so we use a configuration file `triplestore.yaml` to provide details on how to communicate with the triplestore. This file will also provide details of your triple store instance. For example, the configuration below is for a local instance of Oxigraph.

```
url:             http://localhost:7878
query_endpoint:  query
upload_endpoint: store
update_endpoint: update
graph_parameter: graph
default_graph:   default
```

For Blazegraph the equivalent configuration is:

```
url:              http://localhost:9999/blazegraph
query_endpoint:   sparql
upload_endpoint:  sparql
update_endpoint:            
graph_parameter:  context-uri
default_graph:
```

:warning: Note that if using Blazegraph as a triple store the MIME type for triples **must be** `text/rdf+n3` in order to preserve UTF-8 encoding.

### Oxigraph

Two start Oxigraph:

```
oxigraph_server -l . serve 
```

It will now be ready to accept uploads from `upload.php`.

### Blazegraph

Blazegraph can be run using Docker:

```
docker run -d -p 9999:9999 openkbs/blazegraph
```

It will now be ready to accept uploads from `upload.php`.


## Queries

### Find people at an institution that work on fungi

This query uses `RINGGOLD:41803` as the identifier for the Total Botanic Gardens Edinburgh (RBGE), and finds people at the RBGE who have published new fungal names:

```
PREFIX schema: <http://schema.org/>
SELECT DISTINCT ?person ?person_name
FROM <https://www.indexfungorum.org>
FROM <https://orcid.org>
WHERE {
  ?id schema:propertyID "RINGGOLD" .
  ?id schema:value "41803" .
  ?org schema:identifier ?id .
  ?person schema:affiliation ?org .
  ?work schema:creator ?person .
  ?taxonName schema:isBasedOn ?work .
  ?taxonName schema:name ?name .
  {
  ?person schema:givenName ?givenName .
  ?person schema:familyName ?familyName .
    BIND(CONCAT(?givenName, " ", ?familyName) AS ?person_name).
  }
 }
```

