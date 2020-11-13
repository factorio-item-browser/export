local json = require("json")
local dump = {}

--- Adds data to the dump.
-- @param name string: The name of the data.
-- @param data table: The data to add.
function dump.add(name, data)
    if not data then
        return
    end

    print(string.format("#DUMP#%s>%s", name, json.encode(data)))
end

--- Flushes the dump. This will write any data not yet written to the output.
function dump.flush()
end

return dump
