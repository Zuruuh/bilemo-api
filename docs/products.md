# Products

## Entity

Product entity schema:
```ts
{
    "id": int,
    "name": string,
    "os": string,
    "manufacturer": string,
    "price": int,
    "stock": int,
    "storage": int,
    "createdAt": Datetime,
    "lastUpdate": Datetime
}
```

## Routes

![](https://img.shields.io/badge/-GET-brightgreen)  
Route: **/api/products**   
<table>
    <thead>
        <tr>
            <td>Key</td>
            <td>Value</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Description</td>
            <td>Gets a list of products w/ cursor pagination</td>
        </tr>
        <tr>
            <td>Method(s)</td>
            <td>GET</td>
        </tr>
        <tr>
            <td>Security</td>
            <td>On</td>
        </tr>
        <tr>
            <td>Query</td>
            <td>
            Cursor?: int | The current pagination product id (default: 0)
            </td>
        </tr>
        <tr>
            <td>Output</td>
            <td>
            {
             products: Product[]
            }
            </td>
        </tr>
    </tbody>
</table>

![](https://img.shields.io/badge/-GET-brightgreen)  
Route: **/api/products/{id}**   
<table>
    <thead>
        <tr>
            <td>Key</td>
            <td>Value</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Description</td>
            <td>Gets a list of products w/ cursor pagination</td>
        </tr>
        <tr>
            <td>Method(s)</td>
            <td>GET</td>
        </tr>
        <tr>
            <td>Security</td>
            <td>On</td>
        </tr>
        <tr>
            <td>Params</td>
            <td>
            id: int | The searched product's id
            </td>
        </tr>
        <tr>
            <td>Output</td>
            <td>
            {
             product: Product
            }
            </td>
        </tr>
    </tbody>
</table>