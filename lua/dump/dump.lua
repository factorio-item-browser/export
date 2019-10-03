local json = require("json")

local dump = {}

--- Write a dump to the console.
-- @param name string: The name of the dump.
-- @param data table: The data to write into the dump.
function dump.write(name, data)
    print(string.format(">>>%s>>>%s<<<%s<<<", name, json.encode(data), name))
end

return dump
