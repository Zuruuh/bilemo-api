# Response

## Shape

When requesting the server, the response will always be shaped with the following properties:
- **errors**: The errors (if there are any).
- **code**: The http response code.
- **message**: The status of the action.
- **token**: A new JWT that can be used so you don't have to re-create one yourself.

### Errors

An errors object will always be shaped like the following:  
<pre>
{
  "errors": {
    "field": {
      "error": "This field is not correct !"
    },
    "anotherField": {
        "error": "This value is invalid !"
    }
  },
}</pre>

If your request is correct, there should be no errors object in your response.   

