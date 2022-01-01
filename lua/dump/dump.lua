local json = require("json")
local dump = {}

--- Writes data to the dump.
--- @param name string The name of the data.
--- @param data table|nil The data to add.
function dump.write(name, data)
    if not data then
        return
    end

    print(string.format(">DUMP>%s>%s<", name, json.encode(data)))
end

return dump
