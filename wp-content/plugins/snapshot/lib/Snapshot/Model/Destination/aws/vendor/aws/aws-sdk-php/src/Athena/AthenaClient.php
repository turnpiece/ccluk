<?php
namespace Aws\Athena;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Athena** service.
 * @method \Aws\Result batchGetNamedQuery(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchGetNamedQueryAsync(array $args = [])
 * @method \Aws\Result batchGetQueryExecution(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchGetQueryExecutionAsync(array $args = [])
 * @method \Aws\Result createNamedQuery(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createNamedQueryAsync(array $args = [])
 * @method \Aws\Result createWorkGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createWorkGroupAsync(array $args = [])
 * @method \Aws\Result deleteNamedQuery(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteNamedQueryAsync(array $args = [])
 * @method \Aws\Result deleteWorkGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteWorkGroupAsync(array $args = [])
 * @method \Aws\Result getNamedQuery(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getNamedQueryAsync(array $args = [])
 * @method \Aws\Result getQueryExecution(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getQueryExecutionAsync(array $args = [])
 * @method \Aws\Result getQueryResults(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getQueryResultsAsync(array $args = [])
 * @method \Aws\Result getWorkGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getWorkGroupAsync(array $args = [])
 * @method \Aws\Result listNamedQueries(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listNamedQueriesAsync(array $args = [])
 * @method \Aws\Result listQueryExecutions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listQueryExecutionsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result listWorkGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listWorkGroupsAsync(array $args = [])
 * @method \Aws\Result startQueryExecution(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startQueryExecutionAsync(array $args = [])
 * @method \Aws\Result stopQueryExecution(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopQueryExecutionAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateWorkGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateWorkGroupAsync(array $args = [])
 */
class AthenaClient extends AwsClient {}